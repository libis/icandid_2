<template>
    <Content :lang="$ml.current" :contentcode="'aboutcitation'" style="margin-bottom:30px"></Content>
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