# Development with Docker

To get started, just clone this repository and run: 

```bash
docker-compose up -d
```

And then open your WordPress site at `localhost:8002`, walk through the configuration and enable the Wordi plugin.

`docker-compose down` removes the containers, while preserving your database, and `docker-compose down --volumes` removes containers and data.