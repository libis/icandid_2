<template>
    <Content :lang="$ml.current" :contentcode="'abouticandid'"></Content>
</template>
<script>
import Content from '../helpers/Content.vue'
export default {
    components:{
        Content
    }
}
</script>
<style scoped>

</style>