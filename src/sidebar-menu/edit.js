import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, TextControl } from '@wordpress/components';

export default function Edit({ attributes, setAttributes }) {
  const { message } = attributes;
  const blockProps = useBlockProps({
    className: 'chuyennhanali-sidebar-menu-block',
  });

  return (
    <>
      <InspectorControls>
        <PanelBody title={__('Cài đặt', 'nali-custom-block')}>
          <TextControl
            label={__('Thông điệp', 'nali-custom-block')}
            value={message}
            onChange={(value) => setAttributes({ message: value })}
          />
        </PanelBody>
      </InspectorControls>
      <div {...blockProps}>
        <p>{message}</p>
      </div>
    </>
  );
}
