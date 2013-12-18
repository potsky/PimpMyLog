#!/bin/bash
cd `dirname "$0"`
cd ..
base=`pwd`

cd ..
cd PimpMyLog-gh-pages
if [[ $? -ne 0 ]] ; then
	echo "Unable to cd to 'PimpMyLog-gh-pages' from "`pwd`
	exit 1
fi

rm -rf *
cd "$base"
cd "_site"
cp -rf * ../../PimpMyLog-gh-pages/.

cd ../../PimpMyLog-gh-pages/
git add -A .
git commit -m "Auto commit"
git push origin gh-pages
