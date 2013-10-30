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

.. image:: doc/csv.jpg

Each row is one setting. An Setting is changed by a "Handler", and each Handler support up to 3 Parameters.
The next Columns represent the Values for the Environments, and you may use the "DEFAULT" key for a default setting.


Usage
-----------------
The Tool comes with 3 commands.
Just print out the Handler and Values that would be executed:
::
	php dryRun.php devbox ../Settings.csv

Execute the handlers and show status summary:
::
	php apply.php devbox ../Settings.csv

Returns the value for a certain handler. For example - this can be used to get Database values for other scripts:
::
	php value.php devbox ../Settings.csv HandlerName param1 param2 param3

	DB_HOST=`EnvSettingsTool/value.php ${ENVIRONMENT} Settings.csv Est_Handler_XmlFile app/etc/local.xml /config/global/resources/default_setup/connection/host`


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
	* 	If the value field of a row for the current environment is '--delete--' (whtout the quotes) the matched row will be deleted
	* 	param1, param2, or param3 can use the wildcard '%' (without the quotes) instead a concrete values. This will make EnvSettingsTool apply the value to multiple existing rows

*	Est_Handler_MarkerReplace: Simple replaces a given marker in a file
	*	Param1: Relative Path to File (relative to current directory)
	*	Param2: Marker that will be replaced

*	Est_Handler_PrependFileContent: Adds the content from one file to the content of another file
	*	Param1: contentFile path
	*	Param2: targetFile path

Special Features
-----------------
* Skipping rows: if the value field of a row for the current environment is '--skip--' (without the quotes) this handler will not be executed
* The Values also support the special syntax ###ENV:VARIABLE### to read stuff from the (bash) environment Variables.
* Loops: param1, param2 and param3 can specify loops using this syntax: {{1|2|3}}. In this case the same handler will be executed multiple times using every values. \
	It's also possible to have loops in two or all three parameters. In this case all combinations will be executed. \
	Example: \
		Est_Handler_Magento_CoreConfigData('stores', '{{1|2|3}}', 'web/unsecure/base_url') = 'http://www.foo.com' \
	Is equal to: \
		Est_Handler_Magento_CoreConfigData('stores', '1', 'web/unsecure/base_url') = 'http://www.foo.com' \
		Est_Handler_Magento_CoreConfigData('stores', '2', 'web/unsecure/base_url') = 'http://www.foo.com' \
		Est_Handler_Magento_CoreConfigData('stores', '3', 'web/unsecure/base_url') = 'http://www.foo.com' \
* An empty cell falls back the configured DEFAULT column. If you actually need that value to be empty use '--empty--' instead
* You can reference to values from another environment by adding this to the value: '###REF:targetenvironment###'
* ###ENVIRONMENT### will be replaced with current environment name (e.g. "production"). This replacement is done after resolving any references to other environments. So the environment being inserted here is always the actual environment requested and not the one of a referenced value.
* ###PARAM1### will be replaced with the given param1. Also works if the parameter is given in the loop syntax {{..|..}}. Then the individual value will be set.
* ###PARAM2### will be replaced with the given param2. Also works if the parameter is given in the loop syntax {{..|..}}. Then the individual value will be set.
* ###PARAM3### will be replaced with the given param3. Also works if the parameter is given in the loop syntax {{..|..}}. Then the individual value will be set.