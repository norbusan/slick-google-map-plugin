PNAME = slick-google-map
VERSION = 0.2

JSCSS = assets/js/sgmp.tokeninput.js	\
	assets/js/sgmp.framework.js	\
	assets/js/sgmp.admin.js

MINJSCSS = $(patsubst %.js,%.min.js,$(JSCSS))

all: README.md $(MINJSCSS)

$(MINJSCSS): %.min.js: %.js
	yui-compressor $< -o $@

assets/js/jquery.tools.tabs.min.js: assets/js/jquery.tools.tabs.js


README.md: readme.txt
	/usr/local/wp2md/vendor/bin/wp2md convert -i readme.txt -o README.md

# we should check:
# readme.txt:
#	Stable tag: 0.0.1
# slick-google-map.php
#	Version: 0.0.1
zip:
	git archive --format=zip --prefix=${PNAME}/ HEAD > ${PNAME}-${VERSION}.zip

update-pot:
	xgettext 						\
		--language=PHP --keyword=__ --keyword=_e	\
		--sort-by-file					\
		--copyright-holder="Norbert Preining <norbert@preining.info>" \
		--package-name=slick-google-map		\
		--output=languages/slick-google-map.pot	\
		*.php

# the following checks whether all versions agree!
version-check:
	@NV1=`grep "^Stable tag:" readme.txt | awk -F' ' '{print $$NF}'` ;      \
	NV2=`grep "^Version:" slick-google-map.php | awk -F' ' '{print $$NF}'` ;             \
	echo "V1 = $$NV1 (readme.txt)\nV2 = $$NV2 (slick-google-map.php header)"; \
	if [ "$$NV1" != "$$NV2" ] ; then exit 1 ; fi

