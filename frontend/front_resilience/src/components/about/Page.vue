<template>
    <Content :lang="$ml.current" :contentcode="$route.params.page" :key="$route.params.page" style="padding-bottom:20px"></Content>
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