<?php 

class Phones {
    public $db;

    static $FIELDS = array('name', 'settings');

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

    private function _getParent($document_name) {
        $elems = explode('_', $document_name);
        if (sizeof($elems) == 1)
            return false;
        else {
            array_pop($elems);

            $parent_name = $elems[0];
            for ($i=1; $i < sizeof($elems); $i++) { 
                $parent_name = $parent_name . '_' . $elems[$i];
            }

            return $parent_name;
        }
    }

    private function _delDocument($document) {
        if (array_key_exists('children', $document)) {
            foreach ($document['children'] as $child) {
                $doc_child = $this->db->get('copy_defaults', $child);
                $this->_delDocument($doc_child);
            }
        }

        $this->db->delete('factory_defaults', $document['_id']);
    }

    private function _validateAdd($data)
    {
        foreach (authors::$FIELDS as $field) {
            if (!isset($data[$field]))
                throw new RestException(400, "$field field missing");
        }
        return $data;
    }

    private function _validateEdit($data)
    {
        foreach (authors::$FIELDS as $field) {
            if (!isset($data[$field]))
                throw new RestException(400, "$field field missing");
        }
        return $data;
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

        // We need to determine if there is a parent for this element.
        // If it is a family for example, the parent is the brand
        $parent = $this->db->get('factory_defaults', $this->_getParent($document_name), false);
        if (!$parent && $family) {
            // This Exception status code don't seems right...
            throw new RestException(400, "You need to create the parent of this element first. If you are trying to create a device family, make sure that the brand exist");
        } elseif ($parent && $family) {
            if (array_key_exists('children', $parent))
                array_push($parent['children'], $document_name);
            else
                $parent['children'] = array($document_name);
            // updating the parent
            //return !$this->db->update('factory_defaults', $parent['_id'], 'children', $parent['children'], $document_name);
            if ($this->db->update('factory_defaults', $parent['_id'], 'children', $parent['children'], $document_name) === false)
                throw new RestException(500, 'Could not update the parent element');
        }
        
        $object_ready = $this->db->prepareAddPhones($request_data, $document_name, $brand, $family, $model);

        if ($this->db->add('factory_defaults', $object_ready))
            return array('status' => true, 'message' => 'Document successfully added');
        else
            throw new RestException(500, 'Error while Adding the data');
    }

    /**
     *  This is the function that will allow the administrator to delete a brand/family/model/
     *
     * @url DELETE /{brand}
     * @url DELETE /{brand}/{family}
     * @url DELETE /{brand}/{family}/{model}
     */

    function delElement($brand, $family = null, $model = null) {
        $document_name = $this->_buildDocumentName($brand, $family, $model);
        if (!$document_name)
            throw new RestException(400, "Could not find at least the brand");

        $document = $this->db->get('factory_defaults', $document_name, false);
        $this->_delDocument($document);

        return array('status' => true, 'message' => 'Document successfully deleted');
    }
}

?>