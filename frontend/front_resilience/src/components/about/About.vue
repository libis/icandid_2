<template>
    <div style="display:flex">
        <div>
            <Content :lang="$ml.current" contentcode="about" key="about" style="padding-bottom:20px"></Content>
        </div>
        <div>
            <iframe width="560" height="400" src="https://www.youtube.com/embed/Sq87ZJuiVP0?si=omiMvgt8MA9YqLVc" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
        </div>
    </div>
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
 iframe {
    margin-left:30px;
    padding-top:70px
}
</style>