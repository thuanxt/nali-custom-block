import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls, PanelColorSettings } from '@wordpress/block-editor';
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
  const { 
    menuItems = [], 
    menuTitle,
    showTitle,
    backgroundColor,
    textColor,
    activeBackgroundColor,
    activeTextColor,
    titleBackgroundColor,
    titleTextColor,
    hoverBackgroundColor,
    hoverTextColor
  } = attributes;
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
    style: {
      '--menu-bg-color': backgroundColor,
      '--menu-text-color': textColor,
      '--menu-active-bg-color': activeBackgroundColor,
      '--menu-active-text-color': activeTextColor,
      '--menu-hover-bg-color': hoverBackgroundColor,
      '--menu-hover-text-color': hoverTextColor,
    }
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
          <ToggleControl
            label={__('Hiển thị tiêu đề', 'nali-custom-block')}
            checked={showTitle}
            onChange={(value) => setAttributes({ showTitle: value })}
            help={__('Bật/tắt hiển thị tiêu đề menu', 'nali-custom-block')}
          />
        </PanelBody>

        <PanelColorSettings
          title={__('Màu sắc Menu', 'nali-custom-block')}
          colorSettings={[
            {
              value: backgroundColor,
              onChange: (value) => setAttributes({ backgroundColor: value || '#ffffff' }),
              label: __('Màu nền', 'nali-custom-block'),
            },
            {
              value: textColor,
              onChange: (value) => setAttributes({ textColor: value || '#64748b' }),
              label: __('Màu chữ', 'nali-custom-block'),
            },
            {
              value: hoverBackgroundColor,
              onChange: (value) => setAttributes({ hoverBackgroundColor: value || '#f8fafc' }),
              label: __('Màu nền khi hover', 'nali-custom-block'),
            },
            {
              value: hoverTextColor,
              onChange: (value) => setAttributes({ hoverTextColor: value || '#3b82f6' }),
              label: __('Màu chữ khi hover', 'nali-custom-block'),
            },
          ]}
        />

        <PanelColorSettings
          title={__('Màu sắc Menu Active', 'nali-custom-block')}
          colorSettings={[
            {
              value: activeBackgroundColor,
              onChange: (value) => setAttributes({ activeBackgroundColor: value || '#3b82f6' }),
              label: __('Màu nền Active', 'nali-custom-block'),
            },
            {
              value: activeTextColor,
              onChange: (value) => setAttributes({ activeTextColor: value || '#ffffff' }),
              label: __('Màu chữ Active', 'nali-custom-block'),
            },
          ]}
        />

        <PanelColorSettings
          title={__('Màu sắc Tiêu đề', 'nali-custom-block')}
          colorSettings={[
            {
              value: titleBackgroundColor,
              onChange: (value) => setAttributes({ titleBackgroundColor: value }),
              label: __('Màu nền Tiêu đề', 'nali-custom-block'),
            },
            {
              value: titleTextColor,
              onChange: (value) => setAttributes({ titleTextColor: value || '#ffffff' }),
              label: __('Màu chữ Tiêu đề', 'nali-custom-block'),
            },
          ]}
        />

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
                      help={__('Đặt làm menu active mặc định. Sẽ bị vô hiệu khi các trang khác đang được truy cập', 'nali-custom-block')}
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
          {showTitle && (
            <h4 
              className="menu-title"
              style={{
                background: titleBackgroundColor || 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
                color: titleTextColor,
              }}
            >
              {menuTitle}
            </h4>
          )}
          <ul className="menu-list" style={{ backgroundColor: backgroundColor }}>
            {menuItems.map((item, index) => (
              <li 
                key={index} 
                className={`menu-item ${item.isActive ? 'active' : ''}`}
              >
                <a 
                  href={item.url} 
                  className="menu-link"
                  style={{
                    color: item.isActive ? activeTextColor : textColor,
                    backgroundColor: item.isActive ? activeBackgroundColor : 'transparent',
                  }}
                >
                  {item.label}
                </a>
              </li>
            ))}
          </ul>
        </div>
      </div>
    </>
  );
}
