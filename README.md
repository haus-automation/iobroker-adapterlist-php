# ioBroker Adapter-List (PHP)

This is an alternative to

- http://download.iobroker.net/list.html
- https://www.iobroker.net/#en/adapters

## Docker

```
docker build --no-cache -t haus-automation/iobroker-adapterlist-php:8.3-apache .
docker run --rm -ti -p 8000:80 --name iobroker-adapterlist-php -v $(pwd)/index.php:/var/www/html/index.php haus-automation/iobroker-adapterlist-php:8.3-apache
```

Open `http://localhost:8000` afterwards.

## Dependencies

- Docker (optional)
- PHP 8.3
- Bootstrap 5.3.6 (jsDelivr)

## License

The MIT License (MIT)

Copyright (c) 2025 Matthias Kleine <info@haus-automatisierung.com>

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.
