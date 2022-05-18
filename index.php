<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
        
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

            $adapterAuthors = array_values(
                array_map(
                    function($a) {
                        return array_filter(array_map('trim', array_map('strip_tags', $a['authors'])));
                    },
                    $adapterJson
                )
            );

            $authorCount = array_count_values(array_reduce(array_filter($adapterAuthors), 'array_merge', []));
            
            if (isset($_GET['orderBy'])) {
                $orderBy = $_GET['orderBy'];

                if ($orderBy === 'installations') {
                    usort($adapterJson, function($a, $b) { return $a['stat'] < $b['stat'] ? 1 : -1; });
                }
            }
        ?>
        <div class="container-fluid">
            <table class="table table-striped table-sm">
                <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col"><a href="?orderBy=name">Adapter</a></th>
                        <th scope="col">Type</th>
                        <th scope="col"><a href="?orderBy=installations">Installs</a></th>
                        <th scope="col">Authors</th>
                        <th scope="col">Versions</th>
                    </tr>
                </thead>
            <tbody>
                <?php foreach ($adapterJson as $adapter => $info) : ?>
                <?php
                    $versionDate = new \DateTime($info['versionDate']);
                    $versionAge = $versionDate->diff(new \DateTime('now'))->days;
                ?>
                <tr>
                    <th scope="row" class="<?php if ($versionAge > 365) : ?>bg-warning <?php elseif ($info['version'] === $info['stable']) : ?>bg-success<?php endif; ?>"><?= ++$i ?></th>
                    <td>
                        <img src="<?= $info['extIcon'] ?>" class="img-fluid" style="width: 50px;" /> <a href="<?= $info['readme'] ?>"><?= $info['titleLang']['en'] ?: $info['title'] ?></a>
                    </td>
                    <td><?= $info['type'] ?></td>
                    <td>
                        Downloads: <?= $info['stat'] ?: 0 ?><br>
                        Weekly: <?= $info['weekDownloads'] ?: 0 ?>
                    </td>
                    <td>
                        <?php foreach ($info['authors'] as $author) : ?>
                            <?php $author = trim(strip_tags($author)); ?>
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
                <?php endforeach; ?>
            </tbody>
            </table>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" crossorigin="anonymous"></script>
    </body>
</html>
