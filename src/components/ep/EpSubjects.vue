<template>
  <v-data-table
      dense
      :headers="headers"
      :items="desserts"
      item-key="id"
      class="elevation-1"
      :loading="loading"
      loading-text="Loading... Please wait"
  >
    <template v-slot:top>
      <v-toolbar flat>
        <v-toolbar-title>Предмет</v-toolbar-title>
        <v-divider class="mx-4" inset vertical></v-divider>
        <v-spacer></v-spacer>
        <v-btn dark color="light-blue" @click="load">
          Обновить
          <v-icon class="ml-2">mdi-reload</v-icon>
        </v-btn>
        <v-dialog v-model="dialogDelete" max-width="500px">
          <v-card>
            <v-card-title class="text-h5">Are you sure you want to delete this item?</v-card-title>
            <v-card-actions>
              <v-spacer></v-spacer>
              <v-btn color="blue darken-1" text @click="closeDelete">Отмена</v-btn>
              <v-btn color="blue darken-1" text @click="deleteItemConfirm">OK</v-btn>
              <v-spacer></v-spacer>
            </v-card-actions>
          </v-card>
        </v-dialog>
      </v-toolbar>
    </template>
    <template v-slot:[`item.actions`]="{ item }">
      <v-btn small text class="mr-2" :to="{ name: 'ep-subject-edit', params: { id: item.id }}">
        <v-icon>
          mdi-pencil
        </v-icon>
      </v-btn>
      <v-btn small text @click="deleteItem(item)">
        <v-icon>
          mdi-delete
        </v-icon>
      </v-btn>
    </template>
    <template v-slot:[`footer.prepend`]>
      <v-btn color="primary" dark class="mb-2" :to="{ name: 'ep-subject-new' }">
        Добавить
        <v-icon class="ml-2">mdi-plus-box</v-icon>
      </v-btn>
    </template>
  </v-data-table>
</template>

<script>
export default {
  name: "EpSubjects",
  data: () => ({
    headers: [{
      text: '#',
      align: 'start',
      value: 'id',
    },
      { text: 'Название', value: 'name' },
      { text: 'Sort', value: 'sort' },
      { text: 'Actions', value: 'actions', sortable: false },
    ],
    desserts: [],
    loading: true,
    dialogDelete: false,
    editedIndex: -1,
    editedItem: {
      id: 0,
      name: '',
      sort: 100
    },
    defaultItem: {
      id: 0,
      name: '',
      sort: 100
    },
  }),
  methods: {
    load() {
      this.loading = true;
      const formData = new FormData();
      formData.append('f', "list")
      return this.$http.post('/ep/subject.php', formData).then((function (data) {
        this.desserts = data;
        this.loading = false;
      }).bind(this));
    },
    deleteItem(item) {
      this.editedIndex = this.desserts.indexOf(item)
      this.editedItem = Object.assign({}, item)
      this.dialogDelete = true
    },
    deleteItemConfirm() {
      const formData = new FormData();
      formData.append('f', "remove");
      formData.append('id', this.editedItem.id);
      return this.$http.post('/ep/subject.php', formData).then((function (data) {
        if (data.id === this.editedItem.id) {
          let name = this.editedItem.name;
          this.$store.dispatch('snackbar/message', 'Предмет "' + name + '", успешно удален!');
          this.desserts.splice(this.editedIndex, 1);
          this.closeDelete();
        }
      }).bind(this));
    },
    closeDelete() {
      this.dialogDelete = false
      this.$nextTick(() => {
        this.editedItem = Object.assign({}, this.defaultItem)
        this.editedIndex = -1
      })
    },
  },
  watch: {
    dialogDelete (val) {
      val || this.closeDelete()
    },
  },
  mounted () {
    this.load();
  }

}
</script>

<style scoped>

</style>