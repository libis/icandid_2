<template>
    <div class="block">
        <Content id="markdow" :lang="$ml.current" :contentcode="'aboutdatasets'"></Content>
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
<style>
</style>