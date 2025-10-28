import { registerBlockType } from '@wordpress/blocks';
import edit from './edit';

registerBlockType('chuyennhanali/example-block', {
  edit,
  save: () => null, // Dynamic block
});
