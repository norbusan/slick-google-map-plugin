
JSCSS = assets/js/sgmp.tokeninput.js	\
	assets/js/sgmp.framework.js	\
	assets/js/sgmp.admin.js

MINJSCSS = $(patsubst %.js,%.min.js,$(JSCSS))

foo:
	echo JSCSS    = $(JSCSS)
	echo MINJSCSS = $(MINJSCSS)

all: README.md $(MINJSCSS)

$(MINJSCSS): %.min.js: %.js
	yui-compressor $< -o $@

assets/js/jquery.tools.tabs.min.js: assets/js/jquery.tools.tabs.js


README.md: readme.txt
	/usr/local/wp2md/vendor/bin/wp2md convert -i readme.txt -o README.md
