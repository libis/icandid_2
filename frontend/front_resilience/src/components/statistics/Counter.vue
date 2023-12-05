<template>
    <div>
        <span><h1 class="title is-3" v-if="count > 0">{{ $ml.get('totalnumberofarticles') }} : {{ count }}</h1></span>
    </div>
</template>
<script>
import { mapGetters } from "vuex";
import axios from 'axios'
axios.defaults.withCredentials = true;

export default {
    name: "Counter",
    data() {
        return {
            count:0,
        }
    },
    created() {
        this.update();
    },
    methods: {
        update() {
            this.count = 0;
            axios.post(this.getApiStatsUrl+'/counter')
            .then(res => this.count = res.data.hits.total.value)
            /*.catch(error => console.log(error))*/;
        }
    },
    computed: mapGetters(['getApiStatsUrl']),
}
</script>
<style scope>
</style>