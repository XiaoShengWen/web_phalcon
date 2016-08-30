.PHONY: run-web 

PWD := $(shell pwd)
USER := $(shell id -u)
GROUP := $(shell id -g)

all: run-web

run-web: 
	cd docker && sudo docker-compose up

rm-web: 
	cd docker && sudo docker-compose rm

npm:
	sudo docker run -it --rm \
	    -u $(USER):$(GROUP) \
	    -v $(PWD)/www:/var/www/html/www \
	    -w /var/www/html/www \
	    wzy.cloud/library/webpack \
	    npm $(cli) 

webpack:
	sudo docker run -it --rm \
	    -u $(USER):$(GROUP) \
	    -v $(PWD)/www:/var/www/html/www \
	    -w /var/www/html/www \
	    wzy.cloud/library/webpack \
	    webpack $(cli) 

