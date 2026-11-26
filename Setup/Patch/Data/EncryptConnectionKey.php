<?php
declare(strict_types=1);

namespace Picqer\Integration\Setup\Patch\Data;

use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Picqer\Integration\Observer\SendWebhook;

/**
 * Encrypts connection keys that were saved as plain text before the field used the Encrypted backend model.
 * Needed because an encryption key change would otherwise corrupt the plain text value.
 */
class EncryptConnectionKey implements DataPatchInterface
{
    private $moduleDataSetup;
    private $encryptor;

    public function __construct(ModuleDataSetupInterface $moduleDataSetup, EncryptorInterface $encryptor)
    {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->encryptor = $encryptor;
    }

    public function apply(): self
    {
        $connection = $this->moduleDataSetup->getConnection();
        $table = $this->moduleDataSetup->getTable('core_config_data');
        $rows = $connection->fetchAll(
            $connection->select()
                ->from($table, ['config_id', 'value'])
                ->where('path = ?', SendWebhook::XML_PATH_KEY)
        );

        foreach ($rows as $row) {
            $value = trim((string)$row['value']);
            if ($value === '' || preg_match('/^\d+:\d+:/', $value)) {
                continue;
            }
            $connection->update(
                $table,
                ['value' => $this->encryptor->encrypt($value)],
                ['config_id = ?' => $row['config_id']]
            );
        }

        return $this;
    }

    public static function getDependencies(): array
    {
        return [];
    }

    public function getAliases(): array
    {
        return [];
    }
}
