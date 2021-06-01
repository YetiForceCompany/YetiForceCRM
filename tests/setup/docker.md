- [Dockerfile](../../Dockerfile)
- [docker-compose.yml](../../docker-compose.yml)

# Running with [Docker Hub](https://hub.docker.com/r/yetiforce/yetiforcecrm)

## Last Stable Version

```
docker run --name YetiForceCRM-stable -d -p 8000:80 yetiforce/yetiforcecrm:stable
docker exec -it YetiForceCRM-stable /bin/bash
```

## Developer version

```
docker run --name YetiForceCRM-developer -d -p 8000:80 yetiforce/yetiforcecrm:developer
docker exec -it YetiForceCRM-developer /bin/bash
```

# Running with Docker Compose

1. Install [Docker and Docker Compose](https://docs.docker.com/compose/install/)
2. Clone the [repository](https://github.com/YetiForceCompany/YetiForceCRM) from Github.
3. Run `docker-compose up` from the root of repository.
4. Access `http://SERVER_URL:8000` from your web browser to install YetiForceCRM.

```
git clone --depth 1 https://github.com/YetiForceCompany/YetiForceCRM.git
cd YetiForceCRM && docker-compose up -d
docker exec -it YetiForceCRM /bin/bash
```

# Running with Docker Run

1. Install [Docker](http://docs.docker.com/installation/)
2. Run `docker run -d -p 8000:80 -p 8001:22 -p 8002:3306 --name YetiForceCRM yetiforce`
3. Access `http://SERVER_URL:8000` from your web browser to install YetiForceCRM.

```
git clone --depth 1 https://github.com/YetiForceCompany/YetiForceCRM.git
cd YetiForceCRM
docker build --tag yetiforce .
docker run -d -p 8000:80 -p 8001:22 -p 8002:3306 --name YetiForceCRM yetiforce
docker exec -it YetiForceCRM /bin/bash
```

# Docker - startup time

The startup speed depends on the disk speed where docker is running.

- docker-compose = 4 - 10 min 
- Installation of the CRM  (last step) = 1 - 16 min


# Docker - basic information

## Run container in attached mode

```
docker-compose up
```

## Run containers in detached mode

```
docker-compose up -d
```

## Rebuild container

```
docker-compose up --build
```

## Stop container

```
docker stop yetiforce
```

```
docker-compose stop
```

## Remove container

```
docker rm yetiforce
```

```
docker-compose down
```

## Remove image container

```
docker rmi yetiforce
```

## Remove image container

```
docker logs YetiForceCRM
```

## Clean up your containers

```
docker stop YetiForceCRM
docker rm -f YetiForceCRM
docker rmi -f yetiforcecrm_yetiforce-crm
docker rmi -f yetiforce

docker stop $(docker ps -a -q)
docker rm -f $(docker ps -a -q)
docker rmi -f $(docker images -q)
docker network prune -f
docker system prune -a
```
