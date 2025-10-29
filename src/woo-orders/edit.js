import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, RangeControl, ToggleControl, ColorPalette, BaseControl } from '@wordpress/components';

export default function Edit({ attributes, setAttributes }) {
  const { ordersPerPage, showPagination, statusColors } = attributes;
  const blockProps = useBlockProps({
    className: 'chuyennhanali-woo-orders',
  });

  const statusLabels = {
    'pending': __('Chờ xử lý', 'nali-custom-block'),
    'processing': __('Đang xử lý', 'nali-custom-block'),
    'on-hold': __('Tạm giữ', 'nali-custom-block'),
    'completed': __('Hoàn thành', 'nali-custom-block'),
    'cancelled': __('Đã hủy', 'nali-custom-block'),
    'refunded': __('Đã hoàn tiền', 'nali-custom-block'),
    'failed': __('Thất bại', 'nali-custom-block'),
  };

  const updateStatusColor = (status, color) => {
    setAttributes({
      statusColors: {
        ...statusColors,
        [status]: color,
      },
    });
  };

  return (
    <>
      <InspectorControls>
        <PanelBody title={__('Cài đặt', 'nali-custom-block')}>
          <RangeControl
            label={__('Số đơn hàng mỗi trang', 'nali-custom-block')}
            value={ordersPerPage}
            onChange={(value) => setAttributes({ ordersPerPage: value })}
            min={5}
            max={50}
            step={5}
          />
          <ToggleControl
            label={__('Hiển thị phân trang', 'nali-custom-block')}
            checked={showPagination}
            onChange={(value) => setAttributes({ showPagination: value })}
            help={__('Bật/tắt hiển thị nút phân trang', 'nali-custom-block')}
          />
        </PanelBody>

        <PanelBody title={__('Màu sắc trạng thái', 'nali-custom-block')} initialOpen={false}>
          {Object.keys(statusLabels).map((status) => (
            <BaseControl
              key={status}
              label={statusLabels[status]}
              className="woo-status-color-control"
            >
              <ColorPalette
                value={statusColors[status]}
                onChange={(color) => updateStatusColor(status, color || statusColors[status])}
                colors={[
                  { name: 'Amber', color: '#f59e0b' },
                  { name: 'Blue', color: '#3b82f6' },
                  { name: 'Gray', color: '#6b7280' },
                  { name: 'Green', color: '#10b981' },
                  { name: 'Red', color: '#ef4444' },
                  { name: 'Purple', color: '#8b5cf6' },
                  { name: 'Dark Red', color: '#dc2626' },
                  { name: 'Orange', color: '#f97316' },
                  { name: 'Teal', color: '#14b8a6' },
                  { name: 'Pink', color: '#ec4899' },
                ]}
                clearable={false}
              />
            </BaseControl>
          ))}
        </PanelBody>
      </InspectorControls>
      <div {...blockProps}>
        <div className="woo-orders-preview">
          <h3>{__('Danh sách đơn hàng WooCommerce', 'nali-custom-block')}</h3>
          <p>{__('Block này sẽ hiển thị danh sách đơn hàng của người dùng hiện tại.', 'nali-custom-block')}</p>
          <p>
            <strong>{__('Số đơn hàng mỗi trang:', 'nali-custom-block')}</strong> {ordersPerPage}
          </p>
          <p>
            <strong>{__('Phân trang:', 'nali-custom-block')}</strong> {showPagination ? __('Bật', 'nali-custom-block') : __('Tắt', 'nali-custom-block')}
          </p>
          <div className="status-colors-preview">
            <strong>{__('Màu sắc trạng thái:', 'nali-custom-block')}</strong>
            <div style={{ display: 'flex', flexWrap: 'wrap', gap: '8px', marginTop: '8px' }}>
              {Object.keys(statusLabels).map((status) => (
                <span
                  key={status}
                  style={{
                    backgroundColor: statusColors[status],
                    color: '#fff',
                    padding: '4px 12px',
                    borderRadius: '12px',
                    fontSize: '12px',
                    fontWeight: '600',
                  }}
                >
                  {statusLabels[status]}
                </span>
              ))}
            </div>
          </div>
        </div>
      </div>
    </>
  );
}
