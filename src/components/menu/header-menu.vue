<template>
    <v-toolbar-items>
      <template v-for="(item, i) in items">
        <template v-if="item.items">
          <v-menu :key="i" offset-y>
            <template v-slot:activator="{on,attrs}">
              <v-btn text v-bind="attrs" v-on="on">
                <v-icon left v-text="item.icon"></v-icon>
                {{ item.title }}
              </v-btn>
            </template>
            <v-list>
              <v-list-item v-for="(child, index) in item.items" :key="index" :to="child.href">
                <v-list-item-title>
                  <v-icon left v-text="child.icon"></v-icon>
                  {{ child.title }}
                </v-list-item-title>
              </v-list-item>
            </v-list>
          </v-menu>
        </template>
        <template v-else>
          <v-btn :key="i" text :to="item.href">
            <v-icon left v-text="item.icon"></v-icon>
            {{item.title}}
          </v-btn>
        </template>
      </template>
    </v-toolbar-items>
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
  },
}
</script>