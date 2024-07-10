<template>
  <v-list>
    <template v-for="(item, index) in items">
      <template v-if="item.items && item.items.length">
        <v-list-group :key="index" v-model="item.active" :prepend-icon="item.icon" no-action>
          <template v-slot:activator>
            <v-list-item-content>
              <v-list-item-title v-text="item.title"></v-list-item-title>
            </v-list-item-content>
          </template>
          <v-list-item v-for="(child, i) in item.items" :key="i" link :href="child.href">
            <v-list-item-content>
              <v-list-item-title>
                {{child.title}}
                <v-icon class="float-right" v-text="child.icon"></v-icon>
              </v-list-item-title>
            </v-list-item-content>
          </v-list-item>
        </v-list-group>
      </template>
      <template v-else>
        <v-list-item :key="index" link :href="item.href">
          <v-list-item-icon>
            <v-icon v-text="item.icon"></v-icon>
          </v-list-item-icon>
          <v-list-item-title>
            {{item.title}}
          </v-list-item-title>
        </v-list-item>
      </template>
    </template>
  </v-list>
</template>
<script>
export default {
  model: {
    prop: 'items'
  },
  props: {
    items: {
      type: Array,
      default: () => ([])
    }
  }
}
</script>