What is EnvSettingsTool?
========================

EnvSettingsTool offers a concept to adjust settings for applications.
Typically it is used during deployment.
The settings for every Eevironment can be maintained in an CSV file.

CSV File
--------

This is an example CSV file:

| Handler                            | Param1            | Param2                                                 | Param3             | DEFAULT   | devbox | integration | staging | production |
| ---------------------------------- | ----------------- | ------------------------------------------------------ | ------------------ |---------- | ------ | ----------- | ------- | ---------- |
| # Database parameters              |                   |                                                        |                    |           |        |             |         |            |
| Est_Handler_XmlFile                | app/etc/local.xml | /config/global/resources/default_setup/connection/host |                    | localhost |        |             |         |            |
| # Dev settings                     |                   |                                                        |                    |           |        |             |         |            |
| Est_Handler_Magento_CoreConfigData | default           | 0                                                      | dev/debug/profiler | 0         | 1      |             |         |            |

![CSV file](doc/csv.jpg "CSV file")

Each row is one setting. A setting is changed by a "handler", and each handler support up to 3 parameters.
The next columns represent the values for the environments, and you may use the "DEFAULT" key for a default setting.
Empty column values will fall back to the "DEFAULT" column (instead of setting an empty value). If you want to set an empty value instead
configure that cell with `--empty--` and it will set an empty value instead of falling back.


Usage
-----
The tool comes with 3 commands:

### Dry-Run

Just print out the Handler and Values that would be executed:

    php dryRun.php devbox ../settings.csv

### Apply

Execute the handlers and show status summary:

    php apply.php devbox ../settings.csv

### Get single value

Returns the value for a certain handler. For example - this can be used to get database values for other scripts:

    php value.php devbox ../Settings.csv HandlerName param1 param2 param3

Example

    DB_HOST=`EnvSettingsTool/value.php ${ENVIRONMENT} settings.csv Est_Handler_XmlFile app/etc/local.xml /config/global/resources/default_setup/connection/host`

## Example setup script snippet

    echo "Appling settings"
    cd htdocs
    php ../Setup/EnvSettingsTool/apply.php ${ENVIRONMENT} ../Setup/Settings.csv || exit 1


## Handlers

* **Est_Handler_XmlFile**: Can change values in XML

    * Param1: Relative Path to XML File (relative to current directory)
    * Param2: XPath
    * Param3: not used

* **Est_Handler_Magento_CoreConfigData**: Changes values of core_config_data table in a Magento instance. It reads its database parameters from app/etc/local.xml - therefore it needs to be placed after any adjustments of DB credentials.

    * Param1: scope ('default', 'stores', 'websites', or '%')
    * Param2: scopeid (store id, store code, website id, website code, 0 for default scope or '%')
    * Param3: path

    * Special features:
        * If the value field of a row for the current environment is `--delete--` the matched row will be deleted
        * param1, param2, or param3 can use the wildcard `%` instead a concrete values. This will make EnvSettingsTool apply the value to multiple existing rows.
        * If scope is `stores` the scope id can be a store code instead of a store id.
        * If scope is `website` the scope id can be a website code instead of a website id.

* **Est_Handler_MarkerReplace**: Simply replaces a given marker in a file

    * Param1: Relative Path to File (relative to current directory)
    * Param2: Marker that will be replaced
    * Param3: not used

* **Est_Handler_PrependFileContent**: Prepends the content from one file to the content of another file

    * Param1: contentFile path
    * Param2: targetFile path
    * Param3: not used


* **Est_Handler_SetVar**: Allows you to set variables that can be used in all following handlers using `###VAR:<variableName>###`

    * Param1: variable name
    * Param2: not used
    * Param3: not used


## Special Features

### Comments and empty lines

Empty lines or lines starting with '#' or '/' will be ignored. Use this to insert some comments into the csv file.

### Skipping rows

If the value field of a row for the current environment is `--skip--` this handler will not be executed

### Environment variables

The Values also support the special syntax `###ENV:VARIABLE###` to read stuff from the (bash) environment Variables.

### Loops

param1, param2 and param3 can specify loops using this syntax: `{{1|2|3}}`. In this case the same handler will be executed multiple times using every values.
It's also possible to have loops in two or all three parameters. In this case all combinations will be executed.

Example:

    Est_Handler_Magento_CoreConfigData('stores', '{{1|2|3}}', 'web/unsecure/base_url') = 'http://www.foo.com'

Is equal to:

    Est_Handler_Magento_CoreConfigData('stores', '1', 'web/unsecure/base_url') = 'http://www.foo.com'
    Est_Handler_Magento_CoreConfigData('stores', '2', 'web/unsecure/base_url') = 'http://www.foo.com'
    Est_Handler_Magento_CoreConfigData('stores', '3', 'web/unsecure/base_url') = 'http://www.foo.com'

This loop resolution now also works within paramters:

    Est_Handler_Magento_CoreConfigData('stores', '1', 'a/b/{{c|d|e}}') = 'http://www.foo.com'

Is equal to:

    Est_Handler_Magento_CoreConfigData('stores', '1', 'a/b/c') = 'http://www.foo.com'
    Est_Handler_Magento_CoreConfigData('stores', '1', 'a/b/d') = 'http://www.foo.com'
    Est_Handler_Magento_CoreConfigData('stores', '1', 'a/b/e') = 'http://www.foo.com'

### Fallback

An empty cell falls back the configured DEFAULT column. If you actually need that value to be empty use `--empty--` instead.

### References to other environments

You can reference to values from another environment by adding this to the value: `###REF:targetenvironment###`

### Special markers

* `###ENVIRONMENT###` will be replaced with current environment name (e.g. "production"). This replacement is done after resolving any references to other environments. So the environment being inserted here is always the actual environment requested and not the one of a referenced value.
* `###PARAM1###` will be replaced with the given param1. Also works if the parameter is given in the loop syntax `{{..|..}}`. Then the individual value will be set.
* `###PARAM2###` will be replaced with the given param2. Also works if the parameter is given in the loop syntax `{{..|..}}`. Then the individual value will be set.
* `###PARAM3###` will be replaced with the given param3. Also works if the parameter is given in the loop syntax `{{..|..}}`. Then the individual value will be set.


## Tips and tricks

### Delete values

If you're setting Magento core_config_data values and you want to be sure that there's no other value that might interfere with your values (e.g. in a different scope) you can delete all values first:

| Handler                            | Param1  | Param2 | Param3             | DEFAULT    |
| ---------------------------------- | ------- | ------ | ------------------ |----------- |
| Est_Handler_Magento_CoreConfigData | %       | %      | dev/debug/profiler | --delete-- |
| Est_Handler_Magento_CoreConfigData | default | 0      | dev/debug/profiler | 0          |
