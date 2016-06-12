This image is **not intended** for **productive use**! The main purpose is for demo, testing, and development tasks. Therefore a pre-configured shop with [pre-defined options](https://github.com/chrisss404/opencart/blob/master/factory/shop-config.env) yielding fast startup times is used.

### Usage

Create a `docker-compose.yml` with the following contents.

```
web:
  image: chrisss404/opencart:latest-php5.5
  links:
    - db
  ports:
    - "80:80"
db:
  image: chrisss404/opencart:latest-mysql5.5
```

Then run `docker-compose up` and navigate in your browser to

* http://127.0.0.1/ to access the front end, and 
* http://127.0.0.1/admin/ to access the back end.

### Hostname

To set the hostname for OpenCart use the environment variable `VIRTUAL_HOST=foo.bar.com`.

```
web:
  image: chrisss404/opencart:latest-php5.5
  links:
    - db
  ports:
    - "80:80"
  environment:
    - VIRTUAL_HOST=foo.bar.com
db:
  image: chrisss404/opencart:latest-mysql5.5
```

Then run `docker-compose up --force-recreate` and `foo.bar.com` is used as base URL.

### Automated Nginx Reverse Proxy

To use this image in combination with the very popular [nginx reverse proxy](https://hub.docker.com/r/jwilder/nginx-proxy/), use the following `docker-compose.yml`.

```
proxy:
  image: jwilder/nginx-proxy
  ports:
   - "80:80"
  volumes:
   - /var/run/docker.sock:/tmp/docker.sock:ro
web:
  image: chrisss404/opencart:latest-php5.5
  links:
   - db
  environment:
   - VIRTUAL_HOST=foo.bar.com
db:
  image: chrisss404/opencart:latest-mysql5.5
```

Then start both, the reverse proxy and OpenCart, with `docker-compose up --force-recreate`. If the nginx reverse proxy is run with SSL support, add the environment variable `SHOP_USE_SSL=1`.

### Backend Login

To change the backend credentials, set the environment variables `SHOP_ADMIN_USER=joe1` and `SHOP_ADMIN_PASSWORD=abc123`.

```
web:
  image: chrisss404/opencart:latest-php5.5
  links:
    - db
  ports:
    - "80:80"
  environment:
    - SHOP_ADMIN_USER=joe1
    - SHOP_ADMIN_PASSWORD=abc123
db:
  image: chrisss404/opencart:latest-mysql5.5
```

### Plugin Download

To download a plugin on startup, add the environment variable `DOWNLOAD_PLUGIN=https://github.com/foo/bar/archive/master.tar.gz`.

### Development

To use this image for development, use subsequent `docker-compose.yml`.

```
web:
  image: chrisss404/opencart:latest-php5.5
  links:
    - db
  ports:
    - "80:80"
  volumes:
    - ./html:/var/www/html
db:
  image: chrisss404/opencart:latest-mysql5.5
  ports:
    - "3306:3306"
```

Then run the following commands in order to get the files for the volume:

```
# get container id
docker-compose ps -q web

# copy directory to host and set file permissions
docker cp <continer-id>:/var/www/html ./html
chmod -R 0777 html
```
