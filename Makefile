include ./docker/.env
export
export CURRENT_DATE=`date +'%y_%m_%d'`

startDockers:
	docker-compose -f ./docker/docker-compose.yml up -d

fixDockers:
	docker exec -d -it ${MODULE_NAME}-ps1786 rm -f /var/www/html/config/defines_custom.inc.php
	docker exec -d -it ${MODULE_NAME}-ps1782 rm -f /var/www/html/config/defines_custom.inc.php
	docker exec -d -it ${MODULE_NAME}-ps1778 rm -f /var/www/html/config/defines_custom.inc.php
	docker exec -d -it ${MODULE_NAME}-ps1769 rm -f /var/www/html/config/defines_custom.inc.php

zipModule:
	rm -rf build/${CURRENT_DATE}/${MODULE_NAME}; \
	mkdir -p build/${CURRENT_DATE}/${MODULE_NAME}; \
	rsync -av --exclude-from=.zipignore --prune-empty-dirs ./ build/${CURRENT_DATE}/${MODULE_NAME}; \
	cd build/${CURRENT_DATE} && zip -r ${MODULE_NAME}.zip ${MODULE_NAME}; \
	echo "build/${CURRENT_DATE}/${MODULE_NAME}.zip created.";

#todo createChangeLog file
#todo before createVersion commit & push origin main
#todo after createVersion push tag
#todo autoFlow
#	newVersion
#		* createChangeLog and edit
#		* commit & push origin main
#		* create version tag and push tag
#		* create release and build release and upload release
createVersion:
	@if [ -n "$$(git status --porcelain --untracked-files=no)" ]; then \
    	echo "Commit your stage changes before creating a new version."; \
    	exit 1; \
    fi; \
	read -p "Enter version: " VERSION; \
	if git rev-parse "v$$VERSION" >/dev/null 2>&1; then \
		echo "Version $$VERSION already exists. Please enter a different version."; \
		exit 1; \
	fi; \
	if [ ! -f documentation/changelog/v$$VERSION.md ]; then \
		read -p "Version $$VERSION documentation file does not exist. Do you want to create it? [y/N]: " RESPONSE; \
		if [ "$$RESPONSE" = "y" ] || [ "$$RESPONSE" = "Y" ]; then \
			touch documentation/changelog/v$$VERSION.md; \
			git add documentation/changelog/v$$VERSION.md; \
			echo "Created documentation file for version $$VERSION. Please rerun command"; \
			exit 1; \
		else \
			echo "Aborted version creation."; \
			exit 1; \
		fi; \
	fi; \
	git tag -a v$$VERSION -m "Release v$$VERSION"; \
	echo "Version tag $$VERSION created.";

buildVersion:
	@read -p "Enter version: " VERSION; \
	if ! git rev-parse "v$$VERSION" >/dev/null 2>&1; then \
		echo "Version $$VERSION does not exist. Please create a new version first."; \
		exit 1; \
	fi; \
	git archive --format=zip --output=build/${MODULE_NAME}_v$$VERSION.zip v$$VERSION; \
	echo "build/${MODULE_NAME}_v$$VERSION.zip version zipped.";

createRelease:
	@read -p "Enter version: " VERSION; \
	if ! git rev-parse "v$$VERSION" >/dev/null 2>&1; then \
		echo "Version $$VERSION does not exist. Please create a new version first."; \
		exit 1; \
	fi; \
	git push origin v$$VERSION; \
	gh release create v$$VERSION --draft --notes-file documentation/changelog/v$$VERSION.md --notes-start-tag "## Release Notes" --title "v$$VERSION";

uploadRelease:
	@read -p "Enter version: " VERSION; \
    if ! gh release view v$$VERSION >/dev/null 2>&1; then \
        echo "Release $$VERSION does not exist. Please create the release first."; \
        exit 1; \
    fi; \
    gh release upload v$$VERSION build/${MODULE_NAME}_v$$VERSION.zip;

publishRelease:
	@read -p "Enter version: " VERSION; \
    if ! gh release view v$$VERSION >/dev/null 2>&1; then \
        echo "Release $$VERSION does not exist. Please create the release first."; \
        exit 1; \
    fi; \
    gh release edit v$$VERSION --draft=false;