# brea.kfa.st Static Site

## Download the Static Site

To download the entire static site for offline use:

```sh
chmod +x wget-site.sh
./wget-site.sh
```

The site will be downloaded to `./site/brea.kfa.st/`.

## Serve Locally with Docker

To serve the site locally using Docker:

```sh
docker-compose up
```

Then visit [http://localhost:8080](http://localhost:8080) in your browser.

## Deploy to Netlify

- Deploy the contents of `./site/brea.kfa.st/` to Netlify.
- **Do not include** `docker-compose.yml` or `wget-site.sh` in your Netlify deployment.
- Set the publish directory in Netlify to `site/brea.kfa.st`. 