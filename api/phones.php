<?php 

require_once 'wrapper/BigCouch.php';

class Phones {
    public $db;

    function __construct() {
        $this->db = new BigCouch('http://localhost');
    }

    /**
     *  This is the function that will allow the administrator to retrieve all brands/families/models/
     *
     * @url GET /
     * @url GET /{brand}
     * @url GET /{brand}/{family}
     */

    function All($brand = null, $family = null) {
        if (!$brand) {
            $document_type = 'brand';
            $filter_key = null;
        } elseif (!$family) {
            $document_type = 'family';
            $filter_key = $brand;
        } else {
            $document_type = 'model';
            $filter_key = $family;
        }

        $alldoc = $this->db->getAll('factory_defaults', $filter_key);
        return !empty($alldoc['rows']) ? $alldoc['rows'] : array('status' => false, 'message' => 'No data returned');
    }

    /**
     *  This is the function that will allow the administrator to modify a brand/family/model/
     *
     * @url POST /{brand}
     * @url POST /{brand}/{family}
     * @url POST /{brand}/{family}/{model}
     */

    function editMethod($brand, $family = null, $model = null, $request_data = null) {
        if ($model)
            $document = '$brand_$family_$model';
        elseif ($family)
            $document = '$brand_$family';
        elseif ($brand)
            $document = '$brand';
        else
            throw new Exception(400, "Could not find at least the brand");

        foreach ($request_data as $key => $value) {
            if ($this->db->update('factory_defaults', $document, $key, $value))
                return array('status' => true, 'message' => 'Settings successfully modified');
            else
                return array('status' => false, 'message' => 'Error while modifying the data');
        }
    }

    /**
     *  This is the function that will allow the administrator to add a brand/family/model/
     *
     * @url PUT /{brand}
     * @url PUT /{brand}/{family}
     * @url PUT /{brand}/{family}/{model}
     */

    function put($brand, $family, $model, $request_data = null) {

    }

    function delete($brand) {

    }
}

?>