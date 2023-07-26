import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps, RichText, MediaUpload,  InnerBlocks } from '@wordpress/block-editor';

import Edit from './edit';

registerBlockType( 'openlab/activity-block', {
    title: 'OpenLab Activity Block',
    icon: 'buddicons-activity',
    category: 'buddypress',
    attributes: {
        displayStyle: {
            type: 'string',
            default: 'full'
        },
        numItems: {
            type: 'integer',
            default: 5
        },
				source: {
						type: 'string',
						default: 'this-group'
				},
        activities: {
            type: 'array',
            default: []
        }
    },
    edit: Edit,
    save: () => null
} );
