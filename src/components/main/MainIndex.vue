<template>
  <v-list two-line subheader>
    <v-subheader>General</v-subheader>
    <v-list-item link :to="'/main/ou'">
      <v-list-item-content>
        <v-list-item-title>Organizational Unit</v-list-item-title>
        <v-list-item-subtitle>Представляет собой контейнер, который может содержать различные объекты</v-list-item-subtitle>
      </v-list-item-content>
    </v-list-item>
    <v-list-item link :to="'/main/user'">
      <v-list-item-content>
        <v-list-item-title>User</v-list-item-title>
        <v-list-item-subtitle>User</v-list-item-subtitle>
      </v-list-item-content>
    </v-list-item>
    <v-list-item link :to="'/main/group'">
      <v-list-item-content>
        <v-list-item-title>Group</v-list-item-title>
        <v-list-item-subtitle>Group</v-list-item-subtitle>
      </v-list-item-content>
    </v-list-item>
    <v-divider></v-divider>
  </v-list>
</template>

<script>
export default {
  name: "MainIndex"
}
</script>

<style scoped>

</style>