const getGroupIcon = roomType => {
	switch (roomType) {
		case 'crm':
			return 'yfi-favorite-room'
		case 'group':
			return 'yfi-group-room'
		case 'global':
			return 'yfi-global-room'
	}
}

export { getGroupIcon }
