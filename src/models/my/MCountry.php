<?php

class MCountry extends zfx\Model
{

    public function __construct($id = null, $profile = null)
    {
        parent::__construct('country', $profile);

        $this->setSchema();

        if ($id != null) {
            $this->load($id);
        }
    }

    public function setSchema()
    {
        $this->schema->setFields($fields = [
            'Code'           => ['index' => true, 'type' => 'string', 'length' => '', 'value' => ''],
            'Name'           => ['index' => true, 'type' => 'string', 'length' => '', 'value' => ''],
            'Continent'      => ['index' => true, 'type' => 'string', 'length' => '', 'value' => ''],
            'Region'         => ['index' => true, 'type' => 'string', 'length' => '', 'value' => ''],
            'SurfaceArea'    => ['index' => true, 'type' => 'float', 'length' => '', 'value' => ''],
            'IndepYear'      => ['index' => true, 'type' => 'smallint', 'length' => '', 'value' => ''],
            'Population'     => ['index' => true, 'type' => 'int', 'length' => '', 'value' => ''],
            'LifeExpectancy' => ['index' => true, 'type' => 'float', 'length' => '', 'value' => ''],
            'GNP'            => ['index' => true, 'type' => 'float', 'length' => '', 'value' => ''],
            'GNPOld'         => ['index' => true, 'type' => 'float', 'length' => '', 'value' => ''],
            'LocalName'      => ['index' => true, 'type' => 'string', 'length' => '', 'value' => ''],
            'GovernmentForm' => ['index' => true, 'type' => 'string', 'length' => '', 'value' => ''],
            'HeadOfState'    => ['index' => true, 'type' => 'string', 'length' => '', 'value' => ''],
            'Capital'        => ['index' => true, 'type' => 'int', 'length' => '', 'value' => ''],
            'Code2'          => ['index' => true, 'type' => 'string', 'length' => '', 'value' => ''],
        ]);


        $this->schema->setPKey('Code');

        $relations = [
            'city'            => 'CountryCode',
            'countrylanguage' => 'CountryCode',
        ];

        $this->schema->setRelations($relations);
    }
}
