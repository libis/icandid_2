<template>
    <div v-show="content != null">
        <h3 :class="titleclass" v-if="show_title" v-html="title"></h3>
        <div class="contentclass" v-html="content"></div>
    </div>
</template>
<script>
import axios from "axios";
import { mapGetters } from "vuex";
axios.defaults.withCredentials = true;
export default {
    components: {

    },
    props: { contentcode: String, show_title: { type: Boolean, default: true }, lang: String, titleclass: { type: String, default: "title is-2" } },
    data() {
        return {
            data: []
        }
    },
    computed: {
        ...mapGetters(['getApiContentUrl']),
        content() {
            return this.data['content_' + this.lang];
        },
        title() {
            return this.data['title_' + this.lang];
        }
    },
    created() {
        axios
            .get(this.getApiContentUrl + '/' + this.contentcode)
            .then(res => {
                this.data = res.data
            })
            .catch(error => {
                console.log(error)
                let c = "content_" + this.lang
                let t = "title_" + this.lang
                this.data = {};
                this.data[c] = "[ &#9888; Text '" + this.contentcode + "' missing]";
                this.data[t] = "[ &#9888; Error]";
            });
    }
}
</script>
<style>
.contentclass p {
    font-weight: 300;
    font-size: 16px;
    line-height: 22px;
    letter-spacing: 0%;
    margin-bottom: 10px
}

.contentclass a {
    color: #0625AC;
    font-weight: 400;
}

.contentclass ul {
    list-style: disc;
    font-size: 0.8em;
    padding-left: 30px;
}

.contentclass ol {
    font-size: 0.8em;
    padding-left: 30px;
}

.contentclass li {
    padding-bottom: 5px
}
</style>