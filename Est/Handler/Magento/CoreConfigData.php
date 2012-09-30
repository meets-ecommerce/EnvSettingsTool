<?php

/**
 * Parameters
 *
 * - scope
 * - scopeId
 * - path
 */
class Est_Handler_Magento_CoreConfigData extends Est_Handler_AbstractDatabase {

	/**
	 * Protected method that actually applies the settings. This method is implemented in the inheriting classes and
	 * called from ->apply
	 *
	 * @return bool
	 */
	protected function _apply() {

		$scope = $this->param1;
		$scopeId = $this->param2;
		$path = $this->param3;

		if (empty($scope)) {
			throw new Exception("No scope found (param1)");
		}
		if (empty($scopeId)) {
			throw new Exception("No scopeId found (param2)");
		}
		if (empty($path)) {
			throw new Exception("No path found (param2)");
		}

		$conn = $this->getDbConnection();
		$currentValue = $conn->query(sprintf('
			SELECT value
			FROM core_config_data
			WHERE
				scope LIKE "%s"
				AND scope_id LIKE "%s"
				AND path LIKE "%s"',
			$conn->quote($scope),
			$conn->quote($scopeId),
			$conn->quote($path)
		))->fetch();

		var_dump($currentValue);

	}

	/**
	 * Read database connection parameters from local.xml file
	 *
	 * @return array
	 * @throws Exception
	 */
	protected function getDatabaseConnectionParameters() {
		$localXmlFile = 'app/etc/local.xml';

		if (!is_file($localXmlFile)) {
			throw new Exception(sprintf('File "%s" not found', $localXmlFile));
		}

		$config = simplexml_load_file($localXmlFile);
		if ($config === false) {
			throw new Exception(sprintf('Could not load xml file "%s"', $localXmlFile));
		}

		return array(
			'host' => (string)$config->global->resources->default_setup->connection->host,
			'database' => (string)$config->global->resources->default_setup->connection->dbname,
			'username' => (string)$config->global->resources->default_setup->connection->username,
			'password' => (string)$config->global->resources->default_setup->connection->password
		);
	}


}