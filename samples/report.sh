#!/bin/bash

# Run this from the provisioner root directory as samples/report.sh, then you can look in reports/ for a config for every make/family/model of phone

rm -rf reports
mkdir reports

for brand in endpoint/*/; do
	brand=`basename $brand`
	echo $brand
	mkdir reports/$brand
	for family in endpoint/$brand/*/; do
		family=`basename $family`
		echo "      $family"
		mkdir reports/$brand/$family
		for model in `cat endpoint/$brand/$family/family_data.xml | grep '<model>'|perl -pe 's/<.?model>//g'`; do
			echo "            $model"
			php samples/process.php $brand $family $model > reports/${brand}/${family}/${model}.txt
		done
	done
done
