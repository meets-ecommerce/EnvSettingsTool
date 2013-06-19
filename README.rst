What is EnvSettingsTool?
=====================

EnvSettingsTool offers a concept to adjust Settings for Applications.
Typically it is used during deployment.
The Settings for every Environment can be maintained in an CSV file.

Build status: |buildStatusIcon|

CSV File
-------------

This is an example CSV file:
::
	Handler,Param1,Param2,Param3,DEFAULT,latest,deploy,sandbox,staging,production
	,,,,,,,,,
	# Database parameters,,,,,,,,,
	Est_Handler_XmlFile,app/etc/local.xml,/config/global/resources/default_setup/connection/host,,localhost,latestdb,deploydb,,,
	,,,,,,,,,
	# Dev settings,,,,,,,,,
	Est_Handler_Magento_CoreConfigData,default,0,dev/debug/profiler,0,0,0,0,,

.. image:: docs/csv.jpg

Each row is one setting. An Setting is changed by a "Handler", and each Handler support up to 3 Parameters.
The next Columns represent the Values for the Environments, and you may use the "DEFAULT" key for a default setting.

The Values also support the special syntax ###ENV:VARIABLE### to read stuff from the (bash) environment Variables.

Usage
-----------------
Setup.sh:
::
	echo "Apply Settings from ../Setup/Settings.csv for Magento Instance"
	echo "--------------"
	cd htdocs
	# Export some variables that are used in CSV file - e.g. ###ENV:DATABASENAME###
	export DATABASENAME="mydatabasename"
	php ../Setup/EnvSettingsTool/apply.php ${ENVIRONMENT} ../Setup/Settings.csv || exit 1

Handlers
-----------------
List of Handlers:

* 	Est_Handler_XmlFile: Can change values in XML

	*	Param1: Relative Path to XML File (relative to current directory)
	*	Param2: XPath
	*	Param3: Not used

* 	Est_Handler_Magento_CoreConfigData: Changes values of core_config_data table in a  Magento instance.
	It reads its database parameters from app/etc/local.xml - therefore it needs to be placed after any adjustments of DB Credentials.

	*	Param1: scope
	*	Param2: scopeid
	*	Param3: path

*	Est_Handler_MarkerReplace: Simple replaces a given marker in a file
	*	Param1: Relative Path to File (relative to current directory)
	*	Param2: Marker that will be replaced

*	Est_Handler_PrependFileContent: Adds the content from one file to the content of another file
	*	Param1: contentFile path
	*	Param2: targetFile path

