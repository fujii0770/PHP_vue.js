<template>
  <div id="groupware-receive-plan">
    <top-menu></top-menu>
    <receive-plan></receive-plan>
  </div>
</template>
<script>
import GroupwarePage from "../../../components/portal/GroupwarePage"
import TopMenu from "../../../components/portal/TopMenu";
import ReceivePlan from "../../../components/portal/ReceivePlanComponent"
export default {
  components: {
    TopMenu,
    GroupwarePage,
    ReceivePlan
  },
  data() {
    return {
    }
  },
  
  mounted() {
    
  }
}

</script>
 