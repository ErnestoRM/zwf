<?php

class Country extends \zfx\Controller
{

    public function _main()
    {

        if ($this->_segment(1)) {
            $this->show($this->_segment(1));
        } elseif (!$this->_autoexec()) {
            echo "No se encuentra el método";
        }
    }

    public function show($id)
    {
        $mCountry = new MCountry($id);

        echo "<pre>";
        print_r($mCountry->getValues());
        print_r($mCountry->get('city'));
        print_r($mCountry->get('countrylanguage'));
        echo "</pre>";
    }

    public function showAll()
    {
        $mCountry = new MCountry();
        echo "<pre>";
        print_r($mCountry->getAll());
        echo "</pre>";
    }
    //Happy coding!
    // --------------------------------------------------------------------
}
