<?php

$module = $Params['Module'];
$http       = eZHTTPTool::instance();

$tpl = eZTemplate::factory();

$list = eZContentClass::fetchList( );

$nodeID = $Params['NodeId'];
//eZDebug::writeError($nodeID);
$languageCode = 'ger-DE';
$storageDir = 'extension/ezxmlinstaller/data';
// works

// checking whether node is set
if ( !$nodeID )
{
    $contentINI = eZINI::instance( 'content.ini' );
    $nodeID = $contentINI->variable( 'NodeSettings', 'RootNode' );
}

// checking whether node is numeric
if ( !is_numeric( $nodeID ) )
{
    exit(1);
}

// fetching node and checking whether node exists
$node = eZContentObjectTreeNode::fetch( $nodeID );
if ( !$node )
{
    exit(1);
}

// fetching node subtree
$subTreeCount = $node->subTreeCount();
$subTree = $node->subTree();

// preparing variables for looping
//$openedFPs = array();
$objectList = array();
$objectDataList = array();
$nodeList = array();

// looping through subtree
while ( list( $key, $childNode ) = each( $subTree ) )
{
    $status = true;
    $object = $childNode->attribute( 'object' );
    $classIdentifier = $object->attribute( 'class_identifier' );

// looping through attributes
    foreach ( $object->attribute( 'contentobject_attributes' ) as $attribute )
    {
        $attributeStringContent = $attribute->toString();

        if ( $attributeStringContent != '' )
        {
            switch ( $datatypeString = $attribute->attribute( 'data_type_string' ) )
            {
                case 'ezimage':
                {
                    $content = $attribute->attribute( 'content' );
                    $displayText = $content->displayText();
                    $imageAlias = $content->imageAlias('original');
                    $imagePath = $imageAlias['url'];
                    // here it would be nice to add a check if such file allready exists
                    $success = eZFileHandler::copy( $imagePath, $storageDir . '/' . $imageFile );
                    if ( !$success )
                    {
                        $status = false;
                    }
                    $attributeStringContent = $imageFile;
                } break;

                case 'ezbinaryfile':
                case 'ezmedia':
                {
                    $binaryData = explode( '|', $attributeStringContent );
                    $success = eZFileHandler::copy( $binaryData[0], $storageDir . '/' . $binaryData[1] );
                    if ( !$success )
                    {
                        $status = false;
                    }
                    $attributeStringContent = $binaryData[1];
                } break;

                default:
            }
        }

// cleaning up information and moving attribute content to template variables
        $attributeStringContent = str_replace( '<?xml version="1.0" encoding="utf-8"?>', '', $attributeStringContent );
        $attributeStringContent = str_replace( '<?xml version="1.0"?>', '', $attributeStringContent );
        $objectDataList[$object->attribute( 'id' )][$attribute->attribute( 'id' )] = $attributeStringContent;
    }

// moving object to template variables
    $objectList[$object->attribute( 'id' )] = $object;
    $nodeList[$childNode->attribute( 'node_id' )] = $childNode;

}

// we need to clean up variables here
$tpl->setVariable( 'object_list', $objectList );
$tpl->setVariable( 'object_data_list', $objectDataList );
$tpl->setVariable( 'node_list', $nodeList );
$tpl->setVariable( "parent_node", $nodeID );
$tpl->setVariable( "storage_dir", $storageDir );
$tpl->setVariable( "sub_tree", $subTree );
$tpl->setVariable( "sub_tree_count", $subTreeCount );

$result = $tpl->fetch( 'design:xmlexport/content.tpl' );

$doc = new DOMDocument;
$doc->loadXML( $result );

eZExecution::cleanup();
eZExecution::setCleanExit();
header('Content-Type: text/xml');
//header('Content-Type: text/html');
//header('Content-Type: text/txt');
header('Pragma: no-cache' );
header('Expires: 0' );

//echo $doc->saveXML();
echo $result;
//eZDebug::writeError($subTree);
//eZDebug::writeError($objectDataList);
//eZDebug::writeError($result);

exit(0);

?>
