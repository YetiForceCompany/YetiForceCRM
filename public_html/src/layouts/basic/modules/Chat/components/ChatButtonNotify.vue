<!-- /* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */ -->
<template>
	<q-btn :loading="isWaitingForPermission" :color="isDesktopNotification ? 'info' : ''" dense round flat @click="toggleDesktopNotification()">
		<YfIcon :style="styles" :size="size" :icon="isDesktopNotification && isNotificationPermitted() ? 'yfi-chat-notification-on' : 'yfi-chat-notification-off'" />
		<q-tooltip>{{ translate('JS_CHAT_NOTIFICATION') }}</q-tooltip>
	</q-btn>
</template>
<script>
import { createNamespacedHelpers } from 'vuex'
const { mapGetters, mapMutations } = createNamespacedHelpers('Chat')

export default {
	name: 'ChatButtonNotify',
	props: {
		size: {
			type: String,
		},
		styles: {
			type: Object,
		},
	},
	data() {
		return {
			isWaitingForPermission: false,
		}
	},
	computed: {
		...mapGetters(['isDesktopNotification', 'config']),
	},
	methods: {
		...mapMutations(['setDesktopNotification']),
		isNotificationPermitted() {
			return typeof Notification !== 'undefined' && Notification.permission === 'granted'
		},
		toggleDesktopNotification() {
			if (!this.isDesktopNotification && !this.isNotificationPermitted()) {
				this.isWaitingForPermission = true
				PNotifyDesktop.permission()
				setTimeout(() => {
					if (!this.isNotificationPermitted()) {
						app.showNotify({
							text: app.vtranslate('JS_NO_DESKTOP_PERMISSION'),
							type: 'info',
							animation: 'show',
						})
					} else {
						this.setDesktopNotification(!this.isDesktopNotification)
					}
					this.isWaitingForPermission = false
				}, 3000)
			} else if (this.isNotificationPermitted()) {
				this.setDesktopNotification(!this.isDesktopNotification)
			}
		},
	},
	created() {
		if (this.isDesktopNotification && !this.isNotificationPermitted()) {
			this.setDesktopNotification(false)
		}
	},
}
</script>
<style lang="sass"></style>
