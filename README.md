# brea.kfa.st Static Site

## Serve Locally with Docker

To serve the site locally using Docker:

```sh
docker-compose up
```

Then visit [http://localhost:8080](http://localhost:8080) in your browser.

## Deploy to Netlify

- Deploy the contents of `./site` to Netlify.
- **Do not include** `docker-compose.yml` in your Netlify deployment.
- Set the publish directory in Netlify to `site`. 