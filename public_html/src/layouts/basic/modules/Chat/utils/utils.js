const getGroupIcon = roomType => {
	switch (roomType) {
		case 'crm':
			return 'mdi-star'
		case 'group':
			return 'mdi-account-multiple'
		case 'global':
			return 'mdi-account-group'
	}
}

export { getGroupIcon }
