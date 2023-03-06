import { CommonActions, useNavigation } from '@react-navigation/native';
import { AlertDialog, Button, Center, ChevronLeftIcon, CloseIcon, Pressable } from 'native-base';
import React from 'react';

import { translate } from '../../translations/translations';
import { SEARCH } from '../../util/search';

export const UnsavedChangesBack = (props) => {
     const { updateSearch, discardChanges } = props;
     const navigation = useNavigation();
     const [isOpen, setIsOpen] = React.useState(false);
     const onClose = () => setIsOpen(false);
     const cancelRef = React.useRef(null);

     function getStatus() {
          const hasPendingChanges = SEARCH.hasPendingChanges;
          if (hasPendingChanges) {
               // if pending changes found, pop alert to confirm close
               setIsOpen(true);
          } else {
               // if no pending changes, just close it
               navigation.dispatch(CommonActions.goBack());
          }
     }

     // update parameters, then go to search results screen
     const updateClose = () => {
          updateSearch(false, true);
          SEARCH.hasPendingChanges = false;
     };

     // remove pending parameters, then go back to original search results screen
     const forceClose = () => {
          discardChanges();
          setIsOpen(false);
          SEARCH.hasPendingChanges = false;
          navigation.dispatch(CommonActions.goBack());
     };

     return (
          <Center>
               <Pressable onPress={() => getStatus()} hitSlop={{ top: 12, bottom: 12, left: 12, right: 12 }} ml={3}>
                    <ChevronLeftIcon size={5} color="primary.baseContrast" />
               </Pressable>
               <AlertDialog leastDestructiveRef={cancelRef} isOpen={isOpen} onClose={onClose}>
                    <AlertDialog.Content>
                         <AlertDialog.Header>{translate('filters.unsaved_changes')}</AlertDialog.Header>
                         <AlertDialog.Body>{translate('filters.unsaved_changes_body_back')}</AlertDialog.Body>
                         <AlertDialog.Footer>
                              <Button.Group space={3}>
                                   <Button colorScheme="primary" onPress={updateClose} ref={cancelRef}>
                                        {translate('filters.update_filters')}
                                   </Button>
                                   <Button colorScheme="danger" variant="ghost" onPress={forceClose}>
                                        {translate('filters.continue_anyway')}
                                   </Button>
                              </Button.Group>
                         </AlertDialog.Footer>
                    </AlertDialog.Content>
               </AlertDialog>
          </Center>
     );
};

export const UnsavedChangesExit = (props) => {
     const { updateSearch, discardChanges, prevRoute } = props;
     const navigation = useNavigation();
     const [isOpen, setIsOpen] = React.useState(false);
     const onClose = () => setIsOpen(false);
     const cancelRef = React.useRef(null);

     function getStatus() {
          const hasPendingChanges = SEARCH.hasPendingChanges;
          if (hasPendingChanges) {
               // if pending changes found, pop alert to confirm close
               setIsOpen(true);
          } else {
               // if no pending changes, just close it
               navigation.getParent().pop();
          }
     }

     // update parameters, then go to search results screen
     const updateClose = () => {
          updateSearch(false);
          SEARCH.hasPendingChanges = false;
     };

     // remove pending parameters, then go back to original search results screen
     const forceClose = () => {
          discardChanges();
          setIsOpen(false);
          SEARCH.hasPendingChanges = false;
          if (prevRoute === 'SearchScreen') {
               navigation.navigate('SearchTab', {
                    screen: 'SearchResults',
                    params: {
                         term: SEARCH.term,
                    },
               });
          } else {
               navigation.getParent().pop();
          }
     };

     return (
          <Center>
               <Pressable onPress={() => getStatus()} hitSlop={{ top: 12, bottom: 12, left: 12, right: 12 }} ml={3}>
                    <CloseIcon size={5} color="primary.baseContrast" />
               </Pressable>
               <AlertDialog leastDestructiveRef={cancelRef} isOpen={isOpen} onClose={onClose}>
                    <AlertDialog.Content>
                         <AlertDialog.Header>{translate('filters.unsaved_changes')}</AlertDialog.Header>
                         <AlertDialog.Body>{translate('filters.unsaved_changes_body_exit')}</AlertDialog.Body>
                         <AlertDialog.Footer>
                              <Button.Group space={3}>
                                   <Button colorScheme="primary" onPress={updateClose} ref={cancelRef}>
                                        {translate('filters.update_filters')}
                                   </Button>
                                   <Button colorScheme="danger" variant="ghost" onPress={forceClose}>
                                        {translate('filters.continue_anyway')}
                                   </Button>
                              </Button.Group>
                         </AlertDialog.Footer>
                    </AlertDialog.Content>
               </AlertDialog>
          </Center>
     );
};