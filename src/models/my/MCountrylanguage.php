<?php

class MCountrylanguage extends zfx\Model
{

    //------This is a sample code, feel free to change everything you need------------------------------------------
    public function __construct($id = null, $profile = null)
    {

        parent::__construct('countrylanguage', $profile);
        $this->setSchema();

        if ($id != null) {
            $this->load($id);
        }
    }

    //--------------------------------------------------------------------------------------------------------------

    public function setSchema2()
    {

        $this->schema->setFields($fields = [
            'CountryCode' => ['index' => true, 'type' => 'string', 'length' => '', 'value' => ''],
            'Language'    => ['index' => true, 'type' => 'string', 'length' => '', 'value' => ''],
            'IsOfficial'  => ['index' => true, 'type' => 'string', 'length' => '', 'value' => ''],
            'Percentage'  => ['index' => false, 'type' => 'float', 'length' => '', 'value' => ''],
        ]);

        $this->schema->setPKey('Language');
    }
    //--------Happy Coding!-----------------------------------------------------------------------------------------
}
