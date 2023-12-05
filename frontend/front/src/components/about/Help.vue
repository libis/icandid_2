<template>
    <div class="block">
        <Content :lang="$ml.current" :contentcode="'help'"></Content>
    </div>
</template>
<script>
import Content from '../helpers/Content.vue';
export default {
    components: { Content }
}
</script>
<style scoped>
</style>