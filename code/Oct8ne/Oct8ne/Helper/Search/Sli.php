<?php
/**
 * Created by PhpStorm.
 * User: migue
 * Date: 20/06/2017
 * Time: 19:19
 */

namespace Oct8ne\Oct8ne\Helper\Search;

/**
 * TODO: Possible future incorporation
 * Class Sli
 * @package Oct8ne\Oct8ne\Helper\Search
 */
class Sli extends Base
{

    public function getEngineName()
    {
        return "Sli";
    }

    public  function isValidSearchData($searchTerm, $storeI)
    {
        // TODO: Implement isValidSearchData() method.
    }

    public  function search($storeId, $searchTerm, $searchOrder, $searchDir, $page, $pageSize, &$totalSearchResults, &$attrs_applied, &$attrs_available)
    {
        // TODO: Implement search() method.
    }

}