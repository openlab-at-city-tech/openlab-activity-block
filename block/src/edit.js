import { SelectControl, RadioControl, CheckboxControl, PanelBody } from '@wordpress/components';
import { InspectorControls, useBlockProps } from "@wordpress/block-editor";
import { useState, useEffect } from '@wordpress/element';
import ServerSideRender from '@wordpress/server-side-render';

import './editor.scss';

export default function Edit( props ) {
    let { attributes, setAttributes } = props;

    const [ displayStyle, setDisplayStyle ] = useState('full');
    const [ numItems, setNumItems ] = useState(5);
    const [ activities, setActivities ] = useState([]);

    useEffect(() => {
        setDisplayStyle(attributes.displayStyle);
        setNumItems(attributes.numItems);
        setActivities(attributes.activities);
    });

    const allActivities = [
        { value: '', label: 'All Activity' },
        { value: 'created_announcement,created_announcement_reply', label: 'Announcements' },
        { value: 'new_blog_post', label: 'Posts' },
        { value: 'new_blog_comment', label: 'Comments' },
        { value: 'joined_group', label: 'Group Memberships' },
        { value: 'added_group_document', label: 'New Files' },
        { value: 'bp_doc_created', label: 'New Docs' },
        { value: 'bp_doc_edited', label: 'Doc Edits' },
        { value: 'bp_doc_comment', label: 'Doc Comments' },
        { value: 'bbp_topic_create', label: 'New Discussion Topics' },
        { value: 'bbp_reply_create', label: 'Discussion Replies' }
    ];

    function onChangeDisplayStyle( value ) {
        setDisplayStyle(value);
        setAttributes( { displayStyle: value } );
    }

    function onChangeNumItems( value ) {
        setNumItems(parseInt(value));
        setAttributes( { numItems: parseInt(value) } );
    }

    function onChangeActivities( activityValue, isChecked ) {
        var checkedList = [...activities];

        if(isChecked) {
            checkedList = [...activities, activityValue];
        } else {
            checkedList.splice(activities.indexOf(activityValue),1);
        }

        setActivities(checkedList);
        setAttributes( { activities: checkedList } );
    }

    return (
        <div { ...useBlockProps() }>
            <InspectorControls>
                <PanelBody title="OpenLab Activity Block Settings" initialOpen={true}>
                    <div className="olab-ic-field-group">
                        <RadioControl
                            label="Display Style"
                            selected={ displayStyle }
                            options={ [
                                { label: 'Full', value: 'full' },
                                { label: 'Simple', value: 'simple' }
                            ] }
                            onChange={ onChangeDisplayStyle }
                        />
                    </div>
                    <div className="olab-ic-field-group">
                        <SelectControl
                            label="Number of items"
                            value={ numItems }
                            options={[
                                { label: 1, value: 1 },
                                { label: 2, value: 2 },
                                { label: 3, value: 3 },
                                { label: 4, value: 4 },
                                { label: 5, value: 5 },
                                { label: 6, value: 6 },
                                { label: 7, value: 7 },
                                { label: 7, value: 7 },
                                { label: 8, value: 8 },
                                { label: 9, value: 9},
                                { label: 10, value: 10 }
                            ]}
                            onChange={ onChangeNumItems }
                        />
                    </div>
                    <div className="olab-ic-field-group">
                        <label className='olab-ic-field-group-label'>Activities</label>
                        { allActivities.map(
                            (activity) => (
                                <CheckboxControl
                                    key={ activity.value }
                                    label={ activity.label }
                                    value={ activity.value }
                                    checked={ activities.indexOf(activity.value) !== -1 }
                                    onChange={ val => onChangeActivities(activity.value, val) }
                                />
                            )
                        ) }
                    </div>
                </PanelBody>
            </InspectorControls>
            <ServerSideRender
                block="openlab/activity-block"
                attributes={ props.attributes }
            />
        </div>
    )
}