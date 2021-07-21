#Parsing a database dump from wordpress and generating xml with post content

## Description
This is an example of parsing a database dump using the Symfony 4.4 framework and outputting all posts to XML

## Getting Started

### Installation

#### Step 1: Clone project

```bash
git clone https://github.com/neovav/softum.git
```

#### Step 2: Prepare php packages

```bash
cd project
composer install
```

#### Step 3: Copy DB dump files

Copy DB dump files from wordpress to folder: 
```
public/uploads/db
```

#### Step 4: Launch docker containers

```bash
cd ..
docker-compose up -d
```

#### Step 5: Launch browser

Launch your browser and open url: :
```
http://127.0.0.1:8888
```
