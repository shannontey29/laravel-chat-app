APP_ENV=.env

install:
	composer install --no-interaction --prefer-dist --ignore-platform-reqs
	npm ci

setup-env:
	@test -f $(APP_ENV) || cp .env.example $(APP_ENV)
	php artisan key:generate

migrate:
	php artisan migrate --seed --force

compile:
	npm run build

package:
	@mkdir -p build
	zip -r build/chat-app.zip . -x "*.git*" "node_modules/*" "vendor/*" "storage/logs/*" "build/*"
	@echo "Application packaged at build/chat-app.zip"

build: install setup-env migrate compile package
	@echo "Build completed successfully!"
