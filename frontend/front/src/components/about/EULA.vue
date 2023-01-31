<template>
    <div class="block">
        <Content :lang="$ml.current" :contentcode="'eula'"></Content>
        <Content ref="footer" class="bordered" :lang="$ml.current" :contentcode="'eula_footer'" :show_title="false"></Content>
    </div>
</template>
<script>
import Content from '../helpers/Content.vue'
export default {
    components: {
        Content
    }
}
</script>
<style scoped>
.bordered {
    border:1px solid black;
    padding:8px
}
</style>