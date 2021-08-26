dev:
	docker run -it --rm \
		-v "`pwd`":/source \
		-w /source \
		--entrypoint bash \
		php:8
