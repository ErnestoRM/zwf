<?php

class MCity extends zfx\Model
{

    public function __construct($id = null, $profile = null)
    {
        parent::__construct('city', $profile);
        $this->setSchema();

        if ($id != null) {
            $this->load($id);
        }
    }

    public function setSchema()
    {

        $this->schema->setFields($fields = [
            'ID'          => ['index' => true, 'type' => 'int', 'length' => '', 'value' => ''],
            'Name'        => ['index' => true, 'type' => 'string', 'length' => '', 'value' => ''],
            'CountryCode' => ['index' => false, 'type' => 'string', 'length' => '', 'value' => ''],
            'District'    => ['index' => true, 'type' => 'string', 'length' => '', 'value' => ''],
            'Population'  => ['index' => false, 'type' => 'int', 'length' => '', 'value' => ''],
        ]);

        $this->schema->setPKey('ID');
    }
}
