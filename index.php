<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0-beta1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-0evHe/X+R7YkIZDRvuzKMRqM+OrBnVFBL6DOitfPri4tjfHxaWutUpFmBp4vmVor" crossorigin="anonymous">

        <title>ioBroker Adapter List</title>
    </head>
    <body>
        <?php
            $data = null;

            if (!file_exists('sources-dist-latest.json')) {
                $curl = curl_init();
                curl_setopt($curl, CURLOPT_URL, 'http://download.iobroker.net/sources-dist-latest.json');
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
                        <th scope="col"><a href="?orderBy=installations">Installs</a></th>
                        <th scope="col">Authors</th>
                        <th scope="col">Versions</th>
                    </tr>
                </thead>
            <tbody>
                <?php $i = 0; ?>
                <?php foreach ($adapterJson as $adapter => $info) : ?>
                    <?php if ($adapter != 'js-controller') : ?>
                    <?php
                        $versionDate = new \DateTime($info['versionDate']);
                        $versionAge = $versionDate->diff(new \DateTime('now'))->days;
                    ?>
                    <tr>
                        <th scope="row" class="<?php if ($versionAge > 365) : ?>bg-warning <?php elseif (array_key_exists('stable', $info) && $info['version'] === $info['stable']) : ?>bg-success<?php endif; ?>"><?= ++$i ?></th>
                        <td>
                            <img src="<?= $info['extIcon'] ?>" class="img-fluid" style="width: 50px;" />
                            <a href="<?= $info['readme'] ?>"><?= (array_key_exists('titleLang', $info) && is_array($info['titleLang'])) ? $info['titleLang']['en'] : $info['title'] ?></a>
                            (<a href="<?= $info['meta'] ?>">io-package.json</a>)
                        </td>
                        <td><?= $info['type'] ?></td>
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
                            published: <?= (new \DateTime($info['published']))->format('d.m.Y') ?><br>
                            latest: <?= $info['version'] ?> (<?= $versionDate->format('d.m.Y') ?> - <?= $versionAge ?> days)<br>
                            stable: <?= array_key_exists('stable', $info) ? $info['stable'] : '-' ?>
                        </td>
                    </tr>
                    <?php endif; ?>
                <?php endforeach; ?>
            </tbody>
            </table>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0-beta1/dist/js/bootstrap.bundle.min.js" integrity="sha384-pprn3073KE6tl6bjs2QrFaJGz5/SUsLqktiwsUTF55Jfv3qYSDhgCecCxMW52nD2" crossorigin="anonymous"></script>
    </body>
</html>
