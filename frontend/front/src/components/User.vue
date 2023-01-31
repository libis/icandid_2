<template>
    <div class="content">
        <h1 class="title">{{ $ml.get('personalprofile') }}</h1>
        <div class="box">
            <table class="table">
                
                <tr>
                    <td class="has-text-weight-semibold">{{ $ml.get('name') }}</td>
                    <td>{{ getUser.firstname }}</td>
                </tr>
                <tr>
                    <td class="has-text-weight-semibold">{{ $ml.get('lastname') }}</td>
                    <td>{{ getUser.lastname }}</td>
                </tr>
                <tr>
                    <td class="has-text-weight-semibold">{{ $ml.get('email') }}</td>
                    <td>{{ getUser.email }}</td>
                </tr>
                <tr>
                    <td class="has-text-weight-semibold">{{ $ml.get('identifier') }}</td>
                    <td>{{ getUser.eppn }}</td>
                </tr>
                <tr>
                    <td class="has-text-weight-semibold">{{ $ml.get('institution') }}</td>
                    <td>{{ getUser.institution }}</td>
                </tr>
                <tr>
                    <td class="has-text-weight-semibold">{{ $ml.get('researchgroup') }}</td>
                    <td>{{ getUser.researchgroup }}</td>
                </tr>
                <tr>
                    <td class="has-text-weight-semibold">{{ $ml.get('validityperiod') }}</td>
                    <td>{{ getUser.startdate }} -> {{ getUser.enddate }} </td>
                </tr>
                <tr v-if="$parent.userCheckResource('api')">
                    <td class="has-text-weight-semibold">{{ $ml.get('apikey') }}</td>
                    <td>{{ getUser.apikey }}</td>
                </tr>
                                
            </table>
        </div>
    </div>
</template>
<script>
import { mapGetters } from "vuex";
export default {
    computed:{
        ...mapGetters(['getUser'])
    },
    methods: {
        
    },
    created() {
        this.$parent.getUserInfo();
    }
}
</script>