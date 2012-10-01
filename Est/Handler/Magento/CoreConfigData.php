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
		if (is_null($scopeId)) {
			throw new Exception("No scopeId found (param2)");
		}
		if (empty($path)) {
			throw new Exception("No path found (param2)");
		}

		$sqlParameters = array(
			':scope' => $scope,
			':scopeId' => $scopeId,
			':path' => $path
		 );

		$conn = $this->getDbConnection();
		$query = $conn->prepare('SELECT `value` FROM `core_config_data` WHERE `scope` LIKE :scope AND `scope_id` LIKE :scopeId AND `path` LIKE :path');
		$query->execute($sqlParameters);

		$query->setFetchMode(PDO::FETCH_ASSOC);
		$currentValue = $query->fetch();

		$sqlParameters[':value'] = $this->value;

		if ($currentValue === false) {
			// value doesn't exist: insert instead of update

			$res = $conn->prepare('INSERT INTO `core_config_data` (`scope`, `scope_id`, `path`, value) VALUES (:scope, :scopeId, :path, :value)')
				->execute($sqlParameters);

			if ($res === false) {
				// TODO: include speaking error message
				throw new Exception('Error while updating value');
			}

			$this->addMessage(new Est_Message(sprintf('Inserted new value "%s"', $this->value)));

		} elseif($currentValue['value'] == $this->value) {
			$this->addMessage(new Est_Message(sprintf('Value "%s" is already in place. Skipping.', $currentValue['value']), Est_Message::SKIPPED));
		} else {

			$res = $conn->prepare('UPDATE `core_config_data` SET `value` = :value WHERE `scope` LIKE :scope AND `scope_id` LIKE :scopeId AND `path` LIKE :path')
				->execute($sqlParameters);

			if ($res === false) {
				// TODO: include speaking error message
				throw new Exception('Error while updating value');
			}

			$this->addMessage(new Est_Message(sprintf('Updated value from "%s" to "%s"', $currentValue['value'], $this->value)));
		}

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