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

		if ($this->value == '--delete--') {
			$query = $conn->prepare('DELETE FROM `core_config_data` WHERE `scope` LIKE :scope AND `scope_id` LIKE :scopeId AND `path` LIKE :path');
			$res = $query->execute($sqlParameters);

			if ($res === false) {
				throw new Exception('Error while deleting rows');
			}

			$rowCount = $query->rowCount();

			if ($rowCount > 0) {
				$this->addMessage(new Est_Message(sprintf('Deleted "%s" row(s)', $rowCount)));
			} else {
				$this->addMessage(new Est_Message('No rows deleted.', Est_Message::SKIPPED));
			}

		} else {

			$query = $conn->prepare('SELECT `value` FROM `core_config_data` WHERE `scope` LIKE :scope AND `scope_id` LIKE :scopeId AND `path` LIKE :path');
			$query->execute($sqlParameters);

			$query->setFetchMode(PDO::FETCH_ASSOC);
			$currentValue = $query->fetch();

			$containsPlaceholder = $this->containsPlaceholder($sqlParameters);

			$sqlParameters[':value'] = $this->value;

			if ($currentValue === false && $containsPlaceholder) {

				$this->addMessage(new Est_Message('Trying to update using placeholders where no rows existed', Est_Message::SKIPPED));

			} elseif ($currentValue === false) {
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

		return true;
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

	/**
	 * Check if at least one of the paramters contains a wildcard
	 *
	 * @param array $parameters
	 * @return bool
	 */
	protected function containsPlaceholder(array $parameters) {
		foreach ($parameters as $value) {
			if (strpos($value, '%') !== false) {
				return true;
			}
		}
		return false;
	}


}