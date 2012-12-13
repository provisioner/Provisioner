<?php 

class Phones {
    public $db;

    function __construct() {
        $this->db = new BigCouch('http://localhost');
    }

    private function _buildDocumentName($brand, $family = null, $model = null) {
        if ($model)
            return '$brand_$family_$model';
        elseif ($family)
            return '$brand_$family';
        elseif ($brand)
            return '$brand';
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

        if ($this->db->delete('copy_defaults', $document['_id']));
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
        // TODO: The following line need to be tested
        return !empty($alldoc['rows']) ? $alldoc['rows'] : array('status' => false, 'message' => 'No data returned');
    }

    /**
     *  This is the function that will allow the administrator to modify a brand/family/model/
     *
     * @url POST /{brand}
     * @url POST /{brand}/{family}
     * @url POST /{brand}/{family}/{model}
     */

    function editSettings($brand, $family = null, $model = null, $request_data = null) {
        if (empty($request_data))
            throw new Exception(400, "The body for this request cannot be empty");

        $document_name = buildDocumentName($brand, $family, $model);
        if (!$document_name)
            throw new Exception(400, "Could not find at least the brand");

        foreach ($request_data as $key => $value) {
            if ($this->db->update('factory_defaults', $document_name, $key, $value))
                return array('status' => true, 'message' => 'Document successfully modified');
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

    function addDocument($brand, $family = null, $model = null, $request_data = null) {
        if (empty($request_data))
            throw new Exception(400, "The body for this request cannot be empty");

        $document_name = buildDocumentName($brand, $family, $model);
        if (!$document_name)
            throw new Exception(400, "Could not find at least the brand");

        $parent = $this->db->get('factory_defaults', $this->_getParent($document_name));
        $this->db->update('factory_defaults', $document_name, 'children', array_push($parent['children'], $document_name));

        $request_data = json_decode($request_data);
        $request_data->_id = $document_name;

        if ($this->db->add('factory_defaults', $request_data))
            return array('status' => true, 'message' => 'Document successfully added');
        else
            return array('status' => false, 'message' => 'Error while adding the data');
    }

    /**
     *  This is the function that will allow the administrator to delete a brand/family/model/
     *
     * @url DELETE /{brand}
     * @url DELETE /{brand}/{family}
     * @url DELETE /{brand}/{family}/{model}
     */
    function delete($brand, $family = null, $model = null) {
        $document_name = buildDocumentName($brand, $family, $model);
        if (!$document_name)
            throw new Exception(400, "Could not find at least the brand");

        $document = $this->db->get('factory_defaults', $document_name);
        $this->_delDocument($document);
    }
}

?>