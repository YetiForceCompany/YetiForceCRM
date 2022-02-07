<!-- /* {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */ -->
<template>
	<div>
		<div v-show="roomData.showMoreButton" class="text-center q-mt-md">
			<q-btn :loading="fetchingEarlier" icon="mdi-chevron-double-up" @click="$emit('earlierClick')">
				{{ translate('JS_CHAT_EARLIER') }}
				<template #loading>
					<q-spinner-facebook />
				</template>
			</q-btn>
		</div>
		<div class="q-pa-md">
			<template v-for="row in roomData.chatEntries">
				<div :data-id="row.recordid" :key="row.id" @click="messageOnClick ? messageOnClick(row, $event) : ''">
					<transition appear enter-active-class="animate__animated animate__fadeIn" leave-active-class="animate__animated animate__fadeOut">
						<q-chat-message
							:key="row.id"
							:name="header ? header(row) : row.user_name"
							:stamp="row.created"
							:avatar="row.image"
							:text="[row.messages]"
							:bg-color="row.color"
							size="8"
							:sent="row.userid === userId"
						/>
					</transition>
				</div>
			</template>
			<no-results v-show="!areEntries" />
		</div>
	</div>
</template>
<script>
import NoResults from 'components/NoResults.vue'
import { createNamespacedHelpers } from 'vuex'
const { mapGetters, mapActions, mapMutations } = createNamespacedHelpers('Chat')

export default {
	name: 'TabMessages',
	components: { NoResults },
	props: {
		fetchingEarlier: {
			type: Boolean,
			default: false,
		},
		header: {
			type: Function,
		},
		messageOnClick: {
			type: Function,
		},
		roomData: {
			type: Object,
			required: true,
		},
	},
	data() {
		return {
			userId: CONFIG.userId,
		}
	},
	computed: {
		areEntries() {
			return this.roomData.chatEntries === undefined ? true : this.roomData.chatEntries.length
		},
	},
}
</script>
<style lang="sass"></style>
