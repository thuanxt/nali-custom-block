import { __ } from '@wordpress/i18n';
import { useBlockProps } from '@wordpress/block-editor';

export default function Edit() {
	const blockProps = useBlockProps({
		className: 'chuyennhanali-profile-editor-block',
	});

	return (
		<div {...blockProps}>
			<p>{__('Khung chỉnh sửa thông tin người dùng sẽ được hiển thị ở đây.', 'nali-custom-block')}</p>
		</div>
	);
}
