<?xml version = '1.0' encoding = 'UTF-8'?>
{def $parent_node_depth = $sub_tree[0].depth}
{def $current_node_depth = $parent_node_depth}
{def $unset_child = false()}

<eZXMLImporter data_source="{$storage_dir}">
    <CreateContent parentNode="{$parent_node}">

{foreach $sub_tree as $index => $node}

       <ContentObject contentClass="{$node.class_identifier}" section="{$node.object.section_id}" remoteID="{$node.remote_id}" objectID="{$node.contentobject_id}" owner="{$node.object.owner_id}" creator="{$node.creator.id}" sort_field="{$node.sort_field}" sort_order="{$node.sort_order}">
            <Attributes>
            {foreach $node.data_map as $attribute}
                {switch match=$attribute.data_type_string}
                    {case match='ezpage'}
                    <{$attribute.contentclass_attribute_identifier}><![CDATA[{$object_data_list[$node.contentobject_id][$attribute.id]}]]></{$attribute.contentclass_attribute_identifier}>
                    {/case}
                    {case match='ezimage'}
                        <{$attribute.contentclass_attribute_identifier}>{$object_data_list[$object.id][$attribute.id]}</{$attribute.contentclass_attribute_identifier}>
                        <image src="{$attribute.content.original.filename}" title="{$attribute.content.alternative_text}" />
                    {/case}
                    {case match='ezmultioption'}
                    <{$attribute.contentclass_attribute_identifier}><![CDATA[{$object_data_list[$node.contentobject_id][$attribute.id]}]]></{$attribute.contentclass_attribute_identifier}>
                    {/case}
                    {case}
                        <{$attribute.contentclass_attribute_identifier}><![CDATA[{$object_data_list[$node.contentobject_id][$attribute.id]}]]></{$attribute.contentclass_attribute_identifier}>
                    {/case}
                {/switch}
            {/foreach}
            </Attributes>
            <SetReference attribute="node_id" value="{$node.node_id}" />

    {if $index|lt( $sub_tree_count|sub(1) )}
        {if $sub_tree[$index|sum(1)].depth|gt($node.depth)}
            <Childs>
        {elseif $sub_tree[$index|sum(1)].depth|lt($node.depth)}
            {for 1 to $node.depth|sub($sub_tree[$index|sum(1)].depth) as $counter}
                </ContentObject>
                </Childs>
            {/for}
            </ContentObject>
        {else}
            </ContentObject>
        {/if}
    {else}
        {if $node.depth|gt($sub_tree[0].depth)}
        {for 3 to $node.depth as $counter}
            </ContentObject>
            </Childs>
        {/for}
        {/if}
        </ContentObject>
    {/if}
{/foreach}

    </CreateContent>
</eZXMLImporter>
{undef}
