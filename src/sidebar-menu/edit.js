import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { 
  PanelBody, 
  TextControl, 
  Button, 
  Flex, 
  FlexItem,
  ToggleControl,
  __experimentalVStack as VStack 
} from '@wordpress/components';
import { useState } from '@wordpress/element';

export default function Edit({ attributes, setAttributes }) {
  const { menuItems = [], menuTitle } = attributes;
  const [expandedItem, setExpandedItem] = useState(null);

  // Initialize with default menu items if empty
  if (menuItems.length === 0) {
    setAttributes({
      menuItems: [
        {
          label: "Menu 1",
          url: "#",
          isActive: true
        },
        {
          label: "Menu 2",
          url: "#", 
          isActive: false
        },
        {
          label: "Menu 3",
          url: "#",
          isActive: false
        }
      ]
    });
  }

  const blockProps = useBlockProps({
    className: 'chuyennhanali-sidebar-menu-block',
  });

  const updateMenuItem = (index, field, value) => {
    const newMenuItems = [...menuItems];
    newMenuItems[index] = {
      ...newMenuItems[index],
      [field]: value
    };
    setAttributes({ menuItems: newMenuItems });
  };

  const addMenuItem = () => {
    const newMenuItems = [...menuItems];
    newMenuItems.push({
      label: 'Menu mới',
      url: '#',
      isActive: false
    });
    setAttributes({ menuItems: newMenuItems });
  };

  const removeMenuItem = (index) => {
    const newMenuItems = menuItems.filter((_, i) => i !== index);
    setAttributes({ menuItems: newMenuItems });
  };

  const moveMenuItem = (index, direction) => {
    const newMenuItems = [...menuItems];
    const newIndex = direction === 'up' ? index - 1 : index + 1;
    
    if (newIndex >= 0 && newIndex < newMenuItems.length) {
      [newMenuItems[index], newMenuItems[newIndex]] = [newMenuItems[newIndex], newMenuItems[index]];
      setAttributes({ menuItems: newMenuItems });
    }
  };

  return (
    <>
      <InspectorControls>
        <PanelBody title={__('Cài đặt Menu', 'nali-custom-block')} initialOpen={true}>
          <TextControl
            label={__('Tiêu đề Menu', 'nali-custom-block')}
            value={menuTitle}
            onChange={(value) => setAttributes({ menuTitle: value })}
            placeholder={__('Nhập tiêu đề menu...', 'nali-custom-block')}
          />
        </PanelBody>

        <PanelBody title={__('Danh sách Menu', 'nali-custom-block')} initialOpen={true}>
          <VStack spacing={3}>
            {menuItems.map((item, index) => (
              <div key={index} style={{ border: '1px solid #ddd', padding: '12px', borderRadius: '4px' }}>
                <Flex justify="space-between" align="flex-start">
                  <FlexItem>
                    <strong>{item.label || `Menu ${index + 1}`}</strong>
                  </FlexItem>
                  <FlexItem>
                    <Button
                      variant="tertiary"
                      size="small"
                      onClick={() => setExpandedItem(expandedItem === index ? null : index)}
                    >
                      {expandedItem === index ? 'Thu gọn' : 'Chỉnh sửa'}
                    </Button>
                  </FlexItem>
                </Flex>

                {expandedItem === index && (
                  <VStack spacing={2} style={{ marginTop: '12px' }}>
                    <TextControl
                      label={__('Tên menu', 'nali-custom-block')}
                      value={item.label}
                      onChange={(value) => updateMenuItem(index, 'label', value)}
                      placeholder={__('Nhập tên menu...', 'nali-custom-block')}
                    />
                    <TextControl
                      label={__('Link', 'nali-custom-block')}
                      value={item.url}
                      onChange={(value) => updateMenuItem(index, 'url', value)}
                      placeholder={__('Nhập URL hoặc slug page/post...', 'nali-custom-block')}
                      help={__('Có thể nhập URL đầy đủ, slug của page/post WordPress, hoặc # cho placeholder', 'nali-custom-block')}
                    />
                    <ToggleControl
                      label={__('Menu đang được chọn', 'nali-custom-block')}
                      checked={item.isActive}
                      onChange={(value) => updateMenuItem(index, 'isActive', value)}
                      help={__('Menu này sẽ được highlight khi hiển thị', 'nali-custom-block')}
                    />
                    
                    <Flex gap={2}>
                      <Button 
                        variant="secondary" 
                        size="small"
                        onClick={() => moveMenuItem(index, 'up')}
                        disabled={index === 0}
                      >
                        ↑ Lên
                      </Button>
                      <Button 
                        variant="secondary" 
                        size="small"
                        onClick={() => moveMenuItem(index, 'down')}
                        disabled={index === menuItems.length - 1}
                      >
                        ↓ Xuống
                      </Button>
                      <Button 
                        variant="tertiary" 
                        size="small"
                        isDestructive
                        onClick={() => removeMenuItem(index)}
                      >
                        🗑 Xóa
                      </Button>
                    </Flex>
                  </VStack>
                )}
              </div>
            ))}
            
            <Button variant="primary" onClick={addMenuItem}>
              + Thêm menu mới
            </Button>
          </VStack>
        </PanelBody>
      </InspectorControls>

      <div {...blockProps}>
        <div className="chuyennhanali-sidebar-menu-preview">
          <h4 className="menu-title">{menuTitle}</h4>
          <ul className="menu-list">
            {menuItems.map((item, index) => (
              <li key={index} className={`menu-item ${item.isActive ? 'active' : ''}`}>
                <a href={item.url} className="menu-link">
                  {item.label}
                </a>
              </li>
            ))}
          </ul>
          <p className="editor-notice">
            <em>🎨 Đây là preview trong editor. Xem trang thực để thấy kết quả cuối cùng.</em>
          </p>
        </div>
      </div>
    </>
  );
}
