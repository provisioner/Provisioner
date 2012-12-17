<?php 

class Phones {
    public $db;

    private $_FIELDS = array('name', 'settings');

    function __construct() {
        $this->db = new BigCouch(DB_SERVER);
    }

    private function _buildDocumentName($brand, $family = null, $model = null) {
        if ($model)
            return $brand . "_" . $family . "_" . $model;
        elseif ($family)
            return $brand . "_" . $family;
        elseif ($brand)
            return $brand;
        else
            return false;
    }

    /**
     *  This is the function that will allow the administrator to retrieve all brands/families/models/
     *
     * @url GET /
     * @url GET /{brand}
     * @url GET /{brand}/{family}
     * @url GET /{brand}/{family}/{model}
     */

    function getElement($brand = null, $family = null, $model = null) {
        if (!$brand)
            $result = $this->db->getAllByKey('factory_defaults', 'brand', null);
        elseif (!$family)
            $result = $this->db->getAllByKey('factory_defaults', 'family', $brand);
        elseif (!$model)
            $result = $this->db->getAllByKey('factory_defaults', 'model', $family);
        else
            $result = $this->db->get('factory_defaults', $brand . '_' . $family . '_' . $model);

        if (!empty($result))
            return $result;
        else
            throw new RestException(404, "No data found");
    }

    /**
     *  This is the function that will allow the administrator to modify a brand/family/model/
     *
     * @url POST /{brand}
     * @url POST /{brand}/{family}
     * @url POST /{brand}/{family}/{model}
     */

    function editElement($brand, $family = null, $model = null, $request_data = null) {
        if (empty($request_data))
            throw new RestException(400, "The body for this request cannot be empty");

        $document_name = $this->_buildDocumentName($brand, $family, $model);
        if (!$document_name)
            throw new RestException(400, "Could not find at least the brand");

        Validator::validateEdit($request_data, $this->_FIELDS);

        foreach ($request_data as $key => $value) {
            if ($this->db->update('factory_defaults', $document_name, $key, $value))
                return array('status' => true, 'message' => 'Document successfully modified');
            else
                throw new RestException(500, 'Error while modifying the data');
        }
    }

    /**
     *  This is the function that will allow the administrator to add a brand/family/model/
     *
     * @url PUT /{brand}
     * @url PUT /{brand}/{family}
     * @url PUT /{brand}/{family}/{model}
     */

    function addElement($brand, $family = null, $model = null, $request_data = null) {
        if (empty($request_data))
            throw new RestException(400, "The body for this request cannot be empty");

        $document_name = $this->_buildDocumentName($brand, $family, $model);
        if (!$document_name)
            throw new RestException(400, "Could not find at least the brand");
        
        $object_ready = $this->db->prepareAddPhones($request_data, $document_name, $brand, $family, $model);

        if (!$this->db->add('factory_defaults', Validator::validateAdd($object_ready, $this->_FIELDS)))
            throw new RestException(500, 'Error while Adding the data');

        return array('status' => true, 'message' => 'Document successfully added');
    }

    /**
     *  This is the function that will allow the administrator to delete a brand/family/model/
     *
     * @url DELETE /{brand}
     * @url DELETE /{brand}/{family}
     * @url DELETE /{brand}/{family}/{model}
     */

    function delElement($brand, $family = null, $model = null) {
        $this->db->deleteView('factory_defaults', $brand, $family, $model);

        return array('status' => true, 'message' => 'Document successfully deleted');
    }
}

?>