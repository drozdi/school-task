<template>
    <v-data-table
        dense
        :headers="headers"
        :items="desserts"
        item-key="code"
        class="elevation-1"
        :loading="loading"
        loading-text="Loading... Please wait"
    >
      <template v-slot:top>
        <v-toolbar flat>
          <v-toolbar-title>Подразделения</v-toolbar-title>
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
                <v-btn color="blue darken-1" text @click="closeDelete">Cancel</v-btn>
                <v-btn color="blue darken-1" text @click="deleteItemConfirm">OK</v-btn>
                <v-spacer></v-spacer>
              </v-card-actions>
            </v-card>
          </v-dialog>
        </v-toolbar>
      </template>
      <template v-slot:[`item.actions`]="{ item }">
        <v-btn small text class="mr-2" @click="editItem(item)">
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
        <v-dialog v-model="dialog" max-width="500px">
          <template v-slot:activator="{ on, attrs }">
            <v-btn color="primary" dark class="mb-2" v-bind="attrs" v-on="on">
              Добавить
              <v-icon class="ml-2">mdi-plus-box</v-icon>
            </v-btn>
          </template>
          <v-card>
            <v-card-title>
              <span class="text-h5">{{ formTitle }}</span>
            </v-card-title>
            <v-card-text>
              <v-container>
                <v-row>
                  <v-col cols="12">
                    <v-text-field name="ou[code]" v-model="editedItem.code" label="Code"></v-text-field>
                  </v-col>
                  <v-col cols="12">
                    <v-text-field name="ou[description]" v-model="editedItem.description" label="Описание"></v-text-field>
                  </v-col>
                  <v-col cols="12">
                    <v-checkbox name="ou[is_tutors]" v-model="editedItem.is_tutors" label="Могут быть кураторами"></v-checkbox>
                  </v-col>
                  <v-col cols="12">
                    <v-select name="ou[user_id]" :type="'number'" v-model="editedItem.user_id" :items="itemsTutor" label="Ответственный" @change="changeTutor"></v-select>
                  </v-col>
                  <v-col cols="12">
                    <v-text-field name="ou[sort]" v-model="editedItem.sort" label="Sort"></v-text-field>
                  </v-col>
                </v-row>
              </v-container>
            </v-card-text>
            <v-card-actions>
              <v-spacer></v-spacer>
              <v-btn color="blue darken-1" text @click="close">
                Отмена
              </v-btn>
              <v-btn color="blue darken-1" text @click="save">
                Сохранить
              </v-btn>
            </v-card-actions>
          </v-card>
        </v-dialog>
      </template>
    </v-data-table>
</template>

<script>
export default {
  name: "MainOU",
  data: () => ({
    headers: [{
        text: '#',
        align: 'start',
        value: 'id',
      },
      { text: 'Code', value: 'code' },
      { text: 'Ответственный', value: 'tutor' },
      { text: 'Описание', value: 'description' },
      { text: 'Sort', value: 'sort' },
      { text: 'Actions', value: 'actions', sortable: false },
    ],
    desserts: [],
    itemsTutor: [],
    loading: true,
    dialog: false,
    dialogDelete: false,
    editedIndex: -1,
    editedItem: {
      id: 0,
      code: '',
      user_id: null,
      is_tutors: false,
      tutor: '',
      description: '',
      sort: 100
    },
    defaultItem: {
      id: 0,
      code: '',
      user_id: null,
      is_tutors: false,
      tutor: '',
      description: '',
      sort: 100
    },
  }),
  methods: {
    changeTutor (index) {
      this.itemsTutor.forEach((function (t) {
        if (t.value == index) {
          this.editedItem.tutor = t.text;
        }
      }).bind(this));
    },
    load () {
      this.loading = true;
      const formData = new FormData();
      formData.append('f', "list")
      return this.$http.post('/main/ou.php', formData).then((function (data) {
        this.desserts = data;
        this.loading = false;
      }).bind(this));
    },
    loadTutors () {
      const formData = new FormData();
      formData.append('f', "load-tutor");
      return this.$http.post('/main/ou.php', formData).then((function (data) {
        this.itemsTutor = data;
      }).bind(this));
    },
    editItem (item) {
      this.editedIndex = this.desserts.indexOf(item)
      this.editedItem = Object.assign({}, item)
      this.dialog = true
    },
    deleteItem (item) {
      this.editedIndex = this.desserts.indexOf(item)
      this.editedItem = Object.assign({}, item)
      this.dialogDelete = true
    },
    deleteItemConfirm () {
      const formData = new FormData();
      formData.append('f', "remove");
      formData.append('id', this.editedItem.id);
      return this.$http.post('/main/ou.php', formData).then((function (data) {
        if (data.id === this.editedItem.id) {
          let description = this.editedItem.description;
          let code = this.editedItem.code;
          this.$store.dispatch('snackbar/message', 'Подразделение "' + description + ' (' + code + ')", успешно удаленно!');
          this.desserts.splice(this.editedIndex, 1);
          this.closeDelete();
        }
      }).bind(this));
    },
    close () {
      this.dialog = false
      this.$nextTick(() => {
        this.editedItem = Object.assign({}, this.defaultItem)
        this.editedIndex = -1
      })
    },
    closeDelete () {
      this.dialogDelete = false
      this.$nextTick(() => {
        this.editedItem = Object.assign({}, this.defaultItem)
        this.editedIndex = -1
      })
    },
    save () {
      const formData = new FormData();
      formData.append('f', "save");
      formData.append('ou[code]', this.editedItem.code);
      formData.append('ou[user_id]', this.editedItem.user_id);
      formData.append('ou[description]', this.editedItem.description);
      formData.append('ou[sort]', this.editedItem.sort);
      formData.append('ou[is_tutors]', this.editedItem.is_tutors? 1: 0);
      if (this.editedIndex > -1) {
        formData.append('ou[id]', this.editedItem.id);
      }
      return this.$http.post('/main/ou.php', formData).then((function (data) {
        if (this.editedIndex > -1) {
          Object.assign(this.desserts[this.editedIndex], data);
          this.$store.dispatch('snackbar/message', 'Подразделение "' + data.description + ' (' + data.code + ')", успешно изменено!');
        } else {
          this.editedItem = data;
          this.desserts.push(this.editedItem);
          this.$store.dispatch('snackbar/message', 'Подразделение "' + data.description + ' (' + data.code + ')", успешно изменено!');
        }
        this.close();
      }).bind(this));
    },
  },
  computed: {
    formTitle () {
      return this.editedIndex === -1 ? 'New OU' : 'Edit OU - '+this.editedItem.code
    },
  },
  watch: {
    dialog (val) {
      val || this.close()
    },
    dialogDelete (val) {
      val || this.closeDelete()
    },
  },
  mounted () {
     this.loadTutors()
         .finally((function () {
           this.load();
         }).bind(this));
  }
}
</script>

<style scoped>

</style>