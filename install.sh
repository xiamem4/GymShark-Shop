# sudo apt-get install -y software-properties-common
# sudo add-apt-repository ppa:ondrej/php
# sudo apt-get update
# sudo apt-get install php8.2-cli
# sudo apt-get install php8.2-mysql
# sudo apt-get install php8.2-sqlite
# sudo apt-get install php8.2-curl
# sudo apt-get install php8.2-xml
# chmod +x ./install.sh
# chmod +x ./serverRun.sh
php composer.phar config cache-dir --unset
php composer.phar config vendor-dir --unset
php composer.phar config bin-dir --unset
php composer.phar install
