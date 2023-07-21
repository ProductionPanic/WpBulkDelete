#!/bin/bash
# build.sh
# - Build the plugin zip file and remove bloat

THIS_SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" >/dev/null 2>&1 && pwd )"
cd $THIS_SCRIPT_DIR/..

function color {
    COLORNUM="$1"
    shift
    echo -e "\e[38;5;${COLORNUM}m$@\e[0m"
}

function red {
    color 196 "$@"
}
function green {
    color 46 "$@"
}
function yellow {
    color 226 "$@"
}
function blue {
    color 21 "$@"
}
function cyan {
    color 51 "$@"
}
function pink {
    color 201 "$@"
}
function check {
    #display checkmark at the end of the previous line
    echo -en "\r"
    CHECK="\xE2\x9C\x94"
    # echo the checkmark using green color
    echo -e $(green $CHECK)
    wait
}
function br {
    AMOUNT="$1"
    if [ -z "$AMOUNT" ]; then
        AMOUNT=1
    fi
    for i in $(seq 1 $AMOUNT); do
        echo ""
    done
}

function wait {
    sleep 1
}

green "Starting build process..."
pink "-------------------------"
wait
green "#1. Copy files to tmp directory"
blue "    - Creating directory: pp-bulk-delete"
mkdir pp-bulk-delete
check

blue "    - Copying files to pp-bulk-delete excluding node_modules, .git, .github, .gitignore, .gitattributes, .DS_Store and vendor"
rsync -av --progress ./* ./pp-bulk-delete --exclude node_modules --exclude .git --exclude .github --exclude .gitignore --exclude .gitattributes --exclude .DS_Store --exclude vendor > /dev/null 2>&1
check
br 2

green "#2. Install composer dependencies"
blue "    - Installing composer dependencies"
cd pp-bulk-delete
composer install --no-dev --no-interaction --no-progress --optimize-autoloader > /dev/null 2>&1
check
blue "    - Removing composer.lock"
rm composer.lock
check
br 2

green "#3. Install npm dependencies"
blue "    - Installing npm dependencies"
cd public
pnpm install > /dev/null 2>&1
check

blue "    - Building assets"
npm run build > /dev/null 2>&1
check

blue "    - Copy dist to tmp directory"
cd ..
cp -r ./public/dist ./dist 
check

blue "    - Removing public folder contents"
rm -rf ./public/*
check

blue "    - Copying dist to public"
cp -r ./dist ./public/dist
check

blue "    - Removing tmp dist"
rm -rf ./dist
check
br 2

green "#4. Create zip file"
blue "    - Creating zip file"
cd ..
zip -r pp-bulk-delete.zip pp-bulk-delete > /dev/null 2>&1
check

blue "    - Removing tmp directory"
rm -rf pp-bulk-delete
check
br 2

pink "-------------------------"

green "Build complete!"
br 
green "Plugin zip file created: pp-bulk-delete.zip"
br
cyan "To install the plugin, go to your WordPress admin dashboard and navigate to Plugins > Add New > Upload Plugin"

