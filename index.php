<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT" crossorigin="anonymous">

        <title>ioBroker Adapter List</title>
    </head>
    <body>
        <?php
            $data = null;

            $adaptersAge = [];
            $nodeVersions = [];

            if (!file_exists('sources-dist-latest.json')) {
                $curl = curl_init();
                curl_setopt($curl, CURLOPT_URL, 'https://repo.iobroker.live/sources-dist-latest.json');
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($curl, CURLOPT_HEADER, false);
                curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);

                $data = curl_exec($curl);

                curl_close($curl);
    
                file_put_contents('sources-dist-latest.json', $data);
            } else {
                $data = file_get_contents('sources-dist-latest.json');
            }

            $adapterJson = json_decode($data, true);

            function cleanAuthorName($author) {
                return trim(strip_tags(is_array($author) ? $author['name'] : $author));
            }

            $adapterAuthors = array_values(
                array_map(
                    function($adapter) {
                        return array_filter(
                            array_map(
                                'cleanAuthorName',
                                array_key_exists('authors', $adapter) ? $adapter['authors'] : []
                            )
                        );
                    },
                    $adapterJson
                )
            );

            $authorCount = array_count_values(array_reduce(array_filter($adapterAuthors), 'array_merge', []));

            if (isset($_GET['orderBy'])) {
                $orderBy = $_GET['orderBy'];

                if ($orderBy === 'installations') {
                    uasort(
                        $adapterJson,
                        function($a, $b) {
                            $statA = array_key_exists('stat', $a) ? $a['stat'] : 0;
                            $statB = array_key_exists('stat', $b) ? $b['stat'] : 0;

                            return $statA < $statB ? 1 : -1;
                        }
                    );
                } else if ($orderBy === 'type') {
                    uasort(
                        $adapterJson,
                        function($a, $b) {
                            return strcmp($a['type'], $b['type']);
                        }
                    );
                }
            }
        ?>
        <div class="container-fluid">
            <table class="table table-striped table-sm">
                <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col"><a href="?orderBy=name">Adapter</a></th>
                        <th scope="col"><a href="?orderBy=type">Type</a></th>
                        <th scope="col">Node</th>
                        <th scope="col"><a href="?orderBy=installations">Installs</a></th>
                        <th scope="col">Authors</th>
                        <th scope="col">Versions</th>
                    </tr>
                </thead>
            <tbody>
                <?php $i = 0; ?>
                <?php foreach ($adapterJson as $adapter => $info) : ?>
                    <?php if ($adapter != 'js-controller' && $adapter != '_repoInfo') : ?>
                    <?php
                        $versionDate = new \DateTime($info['versionDate']);
                        $versionAge = $versionDate->diff(new \DateTime('now'))->days;

                        $ageCategory = floor($versionAge / 365);

                        if (!array_key_exists($ageCategory, $adaptersAge)) {
                            $adaptersAge[$ageCategory] = 0;
                        }
                        $adaptersAge[$ageCategory]++;
                    ?>
                    <tr>
                        <td class="<?php if ($versionAge > 365) : ?>bg-warning <?php elseif (array_key_exists('stable', $info) && $info['version'] === $info['stable']) : ?>bg-success<?php endif; ?>">
                            #<?= ++$i ?>
                        </td>
                        <td>
                            <img src="<?= $info['extIcon'] ?>" class="img-fluid" style="width: 50px;" />
                            <a href="<?= $info['readme'] ?>"><?= (array_key_exists('titleLang', $info) && is_array($info['titleLang'])) ? $info['titleLang']['en'] : $info['title'] ?></a>
                            (<a href="<?= $info['meta'] ?>">io-package.json</a>)
                        </td>
                        <td>
                            <?= $info['type'] ?>
                        </td>
                        <td>
                            <?php if (array_key_exists('node', $info)) : ?>
                                <?php
                                    $nodeVersion = trim(str_replace(['>=', '.0', '.x'], '', $info['node']));
                                    if (!array_key_exists($nodeVersion, $nodeVersions)) {
                                        $nodeVersions[$nodeVersion] = 0;
                                    }
                                ?>
                                <?php $nodeVersions[$nodeVersion]++ ?>
                                <?= $nodeVersion ?>
                            <?php else : ?>
                                ??
                            <?php endif; ?>
                        </td>
                        <td>
                            Downloads: <?= array_key_exists('stat', $info) ? $info['stat'] : 0 ?><br>
                            Weekly: <?= $info['weekDownloads'] ?: 0 ?>
                        </td>
                        <td>
                            <?php foreach ($info['authors'] as $author) : ?>
                                <?php $author = cleanAuthorName($author); ?>
                                <?php if ($author) : ?>
                                    <?= $author ?> (<?= $authorCount[$author] ?>)<br>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </td>
                        <td>
                            published: <?= (new \DateTime($info['published'] ?: 'now'))->format('d.m.Y') ?><br>
                            latest: <?= array_key_exists('version', $info) ? $info['version'] : '??' ?> (<?= $versionDate->format('d.m.Y') ?> - <?= $versionAge ?> days)<br>
                            stable: <?= array_key_exists('stable', $info) ? $info['stable'] : '-' ?>
                        </td>
                    </tr>
                    <?php endif; ?>
                <?php endforeach; ?>
            </tbody>
            </table>
        </div>

        <p>
            <ul>
                <?php ksort($adaptersAge); ?>
                <?php foreach ($adaptersAge as $years => $count) : ?>
                    <li><strong><= <?= $years + 1 ?> years:</strong> <?= $count ?> (<?= round($count / $i * 100, 0) ?>%)</li>
                <?php endforeach; ?>
            </ul>

            <ul>
            <?php foreach ($nodeVersions as $nodeVersion => $count) : ?>
                <li><strong><?= $nodeVersion ?>:</strong> <?= $count ?> (<?= round($count / $i * 100, 0) ?>%)</li>
            <?php endforeach; ?>
            </ul>
        </p>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js" integrity="sha384-j1CDi7MgGQ12Z7Qab0qlWQ/Qqz24Gc6BM0thvEMVjHnfYGF0rmFCozFSxQBxwHKO" crossorigin="anonymous"></script>
    </body>
</html>
