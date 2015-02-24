# Quick Overview
-More Information can be found at: http://www.provisioner.net

## General Files
autoload.php - Project Autoloader Class

packager.php - takes this project and combines it into tgz packages that can then be used on systems like Endpoint Manager for FreePBX

merge_data.php - Merges all json data into one json export depending on brand & family.

## Samples
parse_csv.php - uses phones.csv to generate TFTPboot files

demo.php - Web based demo

process.php - Web based processor

display.php - Web based GUI

## Tests
phpunittest.php - Requires phpunit (https://github.com/sebastianbergmann/phpunit/) used to test before release

check_json.php - Checks all json files for json error

report.sh - Generates all configs from all brands.

## Includes
json.php - Needed for php less than 5.3

timezone.php - Needed for php less than 5.3
