PNAME    = slick-google-map
VERSION  = $(shell awk -F': ' '/^Stable tag:/ {print $$2}' readme.txt | tr -d ' \r')
HEADERV  = $(shell sed -n 's/^ \* Version: *//p' slick-google-map.php | tr -d ' \r')

.PHONY: all test lint readme version-check zip pot clean

all: version-check lint test readme

test:
	composer test

lint:
	@find . -name '*.php' -not -path './vendor/*' -not -path './.git/*' -print0 \
		| xargs -0 -n1 php -l > /dev/null
	@echo "PHP lint: OK"

readme:
	composer readme

version-check:
	@if [ "$(VERSION)" != "$(HEADERV)" ]; then \
		echo "Version mismatch: readme.txt Stable tag=$(VERSION), plugin header Version=$(HEADERV)"; \
		exit 1; \
	fi
	@echo "Version: $(VERSION) (readme.txt and plugin header agree)"

zip: version-check
	git archive --format=zip --prefix=$(PNAME)/ HEAD > $(PNAME)-$(VERSION).zip
	@echo "Wrote $(PNAME)-$(VERSION).zip"

# Extract translatable strings from PHP and JS into a single .pot file.
# Requires gettext (xgettext). Run when user-visible strings change.
pot:
	@mkdir -p languages
	xgettext --language=PHP \
	         --keyword=__ --keyword=_e --keyword=_x --keyword=_n \
	         --keyword=esc_html__ --keyword=esc_attr__ \
	         --keyword=esc_html_e --keyword=esc_attr_e \
	         --from-code=UTF-8 --add-comments=translators: \
	         --copyright-holder="Norbert Preining <norbert@preining.info>" \
	         --package-name=$(PNAME) \
	         --output=languages/$(PNAME).pot \
	         $$(find . -name '*.php' -not -path './vendor/*' -not -path './tests/*' -not -path './bin/*' -not -path './.git/*')
	xgettext --language=JavaScript \
	         --keyword=__ --keyword=_x --keyword=_n \
	         --from-code=UTF-8 --add-comments=translators: \
	         --join-existing \
	         --output=languages/$(PNAME).pot \
	         $$(find assets -name '*.js' -not -name '*.min.js')

clean:
	rm -rf .phpunit.cache/ .phpunit.result.cache $(PNAME)-*.zip
